<?php


namespace app\home\controller;


use app\common\model\CallHistory;
use app\common\model\Customer;
use app\company\model\Company;
use Curl\Curl;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\Event;
use think\facade\Session;

class HbCall extends \app\common\controller\HomeController
{

    public function callCenter()
    {
//        $str = '2021/06/20';
//        $strTime = strtotime($str);
//        dump((bool)$strTime);
//        $next = date('Y-m-d H:i:s', $strTime + 86400 - 1);
//        dump($next);
//        dump(public_path());

        return $this->view->fetch();
    }

    public function getCallHistory()
    {
        if ($this->request->isPost()) {
            $callHistory = CallHistory::field('called_number')
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
        /**
         * 获取通话记录  暂时放在这里，后期用定时任务实现
         */
//        Event::trigger('CallHistory');

        return $this->view->fetch('hbcall/history_list');
    }

    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $holdertime = $this->request->param('holdertime', '');
            $map = [
                ['user_id', '=', $this->userInfo['id']],
                ['caller_number', '<>', '']
            ];

            $start = strtotime($holdertime);
            if ($start) {
                $end = $start + 86400 - 1;
                $map[] = ['starttime', 'between', [$start, $end]];
            }

            $total = CallHistory::where($map)->count();
            $historyList = CallHistory::with('expense')->where($map)->order('starttime DESC, id DESC')->limit(($page - 1) * $limit, $limit)->select();
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
        $mobile = $this->request->param('mobile');
        $mobile = trim($mobile);
        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            return json(['data' => [], 'msg' => '请输入正确的手机号', 'info' => '温馨提示', 'status' => 0]);
        }
        $userInfo = \app\common\model\User::with('userXnumber')->find($this->userInfo['id']);

        if ($userInfo['phone'] === '') {
            return json(['data' => [], 'msg' => '请前往个人资料中<a href="javascript:;" layuimini-content-href="user/profile.html" data-title="基本资料">填写手机号</a>', 'info' => '温馨提示', 'status' => 0]);
        }

        if (!$userInfo['xnumber']) {
            return json(['data' => [], 'msg' => '未分配小号，请联系管理员分配小号后再重试', 'info' => '温馨提示', 'status' => 0]);
        }

        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $userInfo['phone'],   // 主叫
            'telB' => $mobile,    // 被叫
            'telX' => $userInfo['xnumber'],   // 小号
        ]);
        $response = json_decode($curl->response, true);

        if ($response['code'] === 1000) {
            try {
                $CallHistory = new CallHistory();
                $CallHistory->user_id = $userInfo['id'];
                $CallHistory->username = $userInfo['username'];
                $CallHistory->company_id = $userInfo['company_id'];
                $CallHistory->company = Company::where(['id' => $userInfo['company_id']])->value('username');
                $CallHistory->subid = $response['data']['subid'];
                $CallHistory->caller_number = $userInfo['phone'];
                $CallHistory->axb_number = $userInfo['xnumber'];
                $CallHistory->called_number = $mobile;
                $CallHistory->createtime = time();
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
                $res = Customer::field('title, phone, called_count')
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
                $this->returnData['msg'] = '未找到相关数据';
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
                $this->returnData['msg'] = '导入成功';
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

    public function deleteCustomer ()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('customerList');
            $where = [
                'user_id' => $this->userInfo->id,
            ];

            foreach ($data as $val) {
                $where = array_merge($where, $val);
                Customer::where($where)->delete();
            }
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '清除成功';
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
            Customer::where($where)->inc('called_count')->update();
        }
    }

    public function downloadTemplate()
    {
        $file = './static/images/customer-example-template.xlsx';
        return download($file, '客户管理.xlsx')->expire(300);
    }
}
