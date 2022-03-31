<?php


namespace app\home\controller;


use app\common\model\CallHistory;
use app\common\model\Customer;
use app\company\model\Company;
use Curl\Curl;
use think\db\exception\DbException;
use think\facade\Config;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Filesystem;

class HbCall extends \app\common\controller\HomeController
{
    protected $stopStartDateTime = '2022-03-14 19:00:00';
    protected $stopEndDateTime = '2022-03-14 21:00:00';

    public function callCenter()
    {
//        $str = '2021/06/20';
//        $strTime = strtotime($str);
//        dump((bool)$strTime);
//        $next = date('Y-m-d H:i:s', $strTime + 86400 - 1);
//        dump($next);

        return $this->view->fetch();
    }

    public function getCallHistory()
    {
        if ($this->request->isPost()) {
            $callHistory = CallHistory::with('customer')
//                ->field('called_number, createtime')
                ->where('user_id', '=', $this->userInfo->id)
                ->order('id', 'desc')
                ->limit(20)
                ->select();
            return json(['rows' => $callHistory, 'msg' => '', 'code' => 1]);
        }

        return json($this->returnData);
    }

    public function callHistoryList()
    {
        return $this->view->fetch('hbcall/history_list');
    }

    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $map = [
                ['user_id', '=', $this->userInfo->id],
                ['caller_number', '<>', '']
            ];

            if ($startDate && $endDate) {
                $map[] = ['createtime', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            $total = CallHistory::where($map)->count();
            $historyList = CallHistory::with(['expense', 'customer'])
                ->where($map)
                ->order('createtime DESC, id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }
        return json($this->returnData);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function makeCall()
    {
        if (time() >= strtotime($this->stopStartDateTime) && time() <= strtotime($this->stopEndDateTime)) {
            $this->returnData['msg'] = "由于线路临时升级，呼叫系统 将在{$this->stopStartDateTime}至{$this->stopEndDateTime} 共计两小时暂停服务,给大家带来不便，非常抱歉。感谢大家的支持！";
            return json($this->returnData);
        }

        $mobile = trim($this->request->param('mobile', ''));
        $customerId = $this->request->param('customerId', 0);
        $customer = Customer::find($customerId);

        // 不是试用账号且欠费无法拨号
        if (!$this->userInfo->company->getData('is_test') && $this->userInfo->company->getData('balance') <= 0) {
            $this->returnData['msg'] = lang('您的余额已经不足，为了不影响呼叫，请联系管理员及时充值！');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        // 试用账号到期后无法拨号
        if ($this->userInfo->company->getData('is_test') && time() > $this->userInfo->company->getData('test_endtime')) {
            $this->returnData['msg'] = lang('At the end of the test time, please contact the administrator to recharge and try again');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        if ($this->userInfo->phone === '') {
            $this->returnData['msg'] = '请前往个人资料中<a href="javascript:;" layuimini-content-href="user/profile.html" data-title="基本资料">填写手机号</a>';
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        if (!$this->userInfo->userXnumber) {
            $this->returnData['msg'] = lang('If a small number is not assigned, contact your administrator to assign a small number and try again');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $this->userInfo->phone,   // 主叫
            'telB' => $mobile,    // 被叫
            'telX' => $this->userInfo->userXnumber->numberStore->number,   // 小号
        ]);
        $response = json_decode($curl->response, true);

        if ($response['code'] === 1000) {
            try {
                $CallHistory = new CallHistory();
                $CallHistory->user_id = $this->userInfo->id;
                $CallHistory->username = $this->userInfo->username;
                $CallHistory->company_id = $this->userInfo->company_id;
                $CallHistory->company = $this->userInfo->company->corporation;
                $CallHistory->subid = $response['data']['subid'];
                $CallHistory->caller_number = $this->userInfo->phone;
                $CallHistory->axb_number = $this->userInfo->userXnumber->numberStore->number;
                $CallHistory->called_number = $mobile;
                $CallHistory->createtime = time();
                $CallHistory->customer_id = $customerId;
                $CallHistory->device = $this->agent->device();
                $CallHistory->platform = $this->agent->platform();
                $CallHistory->platform_version = $this->agent->version($this->agent->platform());
                $CallHistory->browser = $this->agent->browser();
                $CallHistory->browser_version = $this->agent->version($this->agent->browser());

                if ($customer) {
                    $CallHistory->customer = $customer->title;
                }

                $CallHistory->save();
            } catch (DbException $dbException) {

            }
        }

        return json($response);
    }

    public function getCustomerList()
    {
        if ($this->request->isPost()) {
            $where = [
                'user_id' => $this->userInfo->id
            ];
            $lastData = Customer::where($where)->order('id', 'desc')->findOrEmpty();
            if ($lastData->toArray()) {
                $lastDateStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->createtime))));
                $lastDateEnd = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->createtime))) + 3600 * 24 - 1);
                $res = Customer::field('id, title, phone, called_count, last_calltime')
                    ->where($where)
                    ->whereBetweenTime('createtime', $lastDateStart, $lastDateEnd)
                    ->order('called_count')
                    ->order('id', 'desc')
                    ->select();
                $this->returnData['data'] = $res;
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = 'success';
                return json($this->returnData);
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function importCustomer()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('customers');
            if (!$data) {
                $this->returnData['msg'] = lang('No data was found');
                return json($this->returnData);
            }

            foreach($data as $key => &$val) {
                $val['createtime'] = time();
                $val['company_id'] = $this->userInfo->company_id;
                $val['user_id'] = $this->userInfo->id;
            }

            try {
                $res = (new Customer())->saveAll($data);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('The import was successful');
                $this->returnData['data'] = $res;

                return json($this->returnData);
            } catch (DbException $dbException) {
                $this->returnData['code'] = $dbException->getCode();
                $this->returnData['msg'] = $dbException->getMessage();
                $this->returnData['data'] = $dbException->getData();

                return json($this->returnData);
            }
        }

        return $this->returnData;
    }

    public function importExcel()
    {
        if ($this->request->isPost()) {
            $file = request()->file('file');
            $data = $this->readExcel($file);
            try {
                $res = (new Customer())->saveAll($data);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('The import was successful');
                $this->returnData['data'] = $res;

                return json($this->returnData);
            } catch (DbException $dbException) {
                $this->returnData['code'] = $dbException->getCode();
                $this->returnData['msg'] = $dbException->getMessage();
                $this->returnData['data'] = $dbException->getData();

                return json($this->returnData);
            }
        }

        return json($this->returnData);
    }

    protected function readExcel($file)
    {
        return readExcel($file, [
            'createtime' => time(),
            'company_id' => $this->userInfo->company_id,
            'user_id' => $this->userInfo->id,
        ]);
    }

    public function deleteCustomer ()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('customerList');
            $where = [
                'user_id' => $this->userInfo->id,
            ];

            if (Customer::where($where)->whereIn('id', $data)->delete()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function updateCustomerCalledCount()
    {
        if ($this->request->isPost()) {
            $phone = $this->request->param('phone');
            $where = [
                'user_id' => $this->userInfo->id,
                'phone' => $phone
            ];
            Customer::where($where)->inc('called_count')->update(['last_calltime' => time()]);
        }
    }

    public function downloadTemplate()
    {
        $file = './static/images/customer-example-template.xlsx';
        return download($file, '客户管理.xlsx')->expire(300);
    }
}
