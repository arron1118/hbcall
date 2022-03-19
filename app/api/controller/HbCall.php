<?php


namespace app\api\controller;


use app\common\model\CallHistory;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;

class HbCall extends \app\common\controller\ApiController
{
    protected $stopStartDateTime = '2022-03-14 19:00:00';
    protected $stopEndDateTime = '2022-03-14 21:00:00';

    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        if (!$this->isLogin()) {
            $this->returnData['code'] = 2;
            $this->returnData['msg'] = '权限不足：未登录';
            return json($this->returnData);
        }

        if (time() >= strtotime($this->stopStartDateTime) && time() <= strtotime($this->stopEndDateTime)) {
            $this->returnData['msg'] = "由于线路临时升级，呼叫系统 将在{$this->stopStartDateTime}至{$this->stopEndDateTime} 共计两小时暂停服务,给大家带来不便，非常抱歉。感谢大家的支持！";
            return json($this->returnData);
        }

        $mobile = $this->request->param('mobile', '');
        $mobile = trim($mobile);

        // 试用账号到期后无法拨号
        if ($this->userInfo->company->getData('is_test') && time() > $this->userInfo->company->getData('test_endtime')) {
            $this->returnData['sub_msg'] = lang('At the end of the test time, please contact the administrator to recharge and try again');
            $this->returnData['msg'] = lang('Tips');
            return json($this->returnData);
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = '呼叫失败';
            $this->returnData['sub_msg'] = lang('Please enter the correct mobile phone number');
            return json($this->returnData);
        }

        if ($this->userInfo->phone === '') {
            $this->returnData['msg'] = '呼叫失败';
            $this->returnData['sub_msg'] = '请先在个人资料中填写手机号';
            return json($this->returnData);
        }
        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $this->userInfo->phone,
            'telB' => $mobile,
            'telX' => $this->userInfo->userXnumber->xnumber
        ]);
        $response = json_decode($curl->response, true);

        if ($response['code'] === 1000) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = $this->userInfo->id;
            $CallHistory->username = $this->userInfo->username;
            $CallHistory->company_id = $this->userInfo->company_id;
            $CallHistory->company = $this->userInfo->company->corporation;
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->caller_number = $this->userInfo->phone;
            $CallHistory->axb_number = $this->userInfo->userXnumber->xnumber;
            $CallHistory->called_number = $mobile;
            $CallHistory->createtime = time();
            $CallHistory->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '拨号成功';
            $this->returnData['data'] = [
                'axb_number' => $this->userInfo->userXnumber->xnumber,
                'mobile' => $mobile,
                'response' => $response
            ];
        } else {
            $this->returnData['msg'] = $response['msg'];
            $this->returnData['sub_msg'] = $response['data'];
        }

        return json($this->returnData);
    }

    /**
     * 获取通话记录 (拨通号码的记录)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHistoryList()
    {
        if (!$this->isLogin()) {
            $this->returnData['msg'] = '权限不足：未登录';
            return json($this->returnData);
        }

        $userInfo = $this->getUserInfo();
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 10);
        $date = $this->request->param('date', '');
        $map = [
            ['user_id', '=', $userInfo['id']],
            ['caller_number', '<>', '']
        ];

        $start = strtotime($date);
        if ($start) {
            $end = $start + 86400 - 1;
            $map[] = ['starttime', 'between', [$start, $end]];
        }

        if ($page <= 0) {
            $page = 1;
        }

        if ($limit <= 0) {
            $limit = 10;
        }

        $total = CallHistory::where($map)->count();
        $historyList = CallHistory::where($map)->order('starttime DESC')->limit(($page - 1) * $limit, $limit)->select();
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '操作成功';
        $this->returnData['data'] = $historyList->visible(['id', 'called_number', 'starttime'])->toArray();
        $this->returnData['total'] = $total;
        return json($this->returnData);
    }

    /**
     * 更新通话记录
     */
    public function updateCallHistory()
    {
//        return date('Y-m-d H:i:s', 1639122363);
        Event::trigger('CallHistory');
        return 'Done!';
    }
}
