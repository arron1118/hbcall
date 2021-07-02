<?php


namespace app\home\controller;


use app\common\model\CallHistory;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Event;
use think\facade\Session;

class HbCall extends \app\common\controller\HomeController
{

    public function callCenter()
    {
        /*$str = '2021/06/20';
        $strTime = strtotime($str);
        dump($strTime);
        $next = date('Y-m-d H:i:s', $strTime + 86400 - 1);
        dump($next);*/

        return $this->view->fetch();
    }

    public function callHistoryList()
    {
        /**
         * 获取通话记录  暂时放在这里，后期用定时任务实现
         */
        Event::trigger('CallHistory');

        return $this->view->fetch('hbcall/history_list');
    }

    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $map = ['user_id' => $this->userInfo['id']];
            $total = CallHistory::where($map)->where('caller_number != ""')->count();
            $historyList = CallHistory::where($map)->where('caller_number != ""')->order('id DESC')->limit(($page - 1) * $limit, $limit)->select();
            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }
    }

    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        $mobile = $this->request->param('mobile');
        $mobile = trim($mobile);
        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            return json(['data' => '请输入正确的手机号', 'info' => '温馨提示', 'status' => 0]);
        }
        $curl = new Curl();
        $curl->post('http://call.hbosw.net/API/axbCallApi.aspx', [
            'mobile' => $mobile,
            'axb_number' => $this->userInfo['axb_number']
        ]);
        $response = json_decode($curl->response, true);
        if ($response['status']) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = $this->userInfo['id'];
            $CallHistory->username = $this->userInfo['username'];
            $CallHistory->company_id = $this->userInfo['company_id'];
            $CallHistory->company = Company::where(['id' => $this->userInfo['company_id']])->value('username');
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->axb_number = $response['data']['axb_number'];
            $CallHistory->called_number = $response['data']['mobile'];
            $CallHistory->save();
        }
        return json($response);
    }
}
