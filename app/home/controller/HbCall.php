<?php


namespace app\home\controller;


use app\common\model\CallHistory;
use Curl\Curl;
use think\facade\Session;

class HbCall extends \app\common\controller\HomeController
{

    public function callCenter()
    {
        return $this->view->fetch();
    }

    public function callHistoryList()
    {
        return $this->view->fetch('hbcall/history_list');
    }

    public function makeCall()
    {
        $mobile = $this->request->param('mobile');
        $mobile = trim($mobile);
        /*if (!$mobile || strlen($mobile) < 11) {
            return json(['error']);
        }*/
        $curl = new Curl();
        $curl->post('http://call.hbosw.net/API/axbCallApi.aspx', [
            'mobile' => $mobile,
            'axb_number' => $this->axb_number
        ]);
        $response = json_decode($curl->response, true);
        if ($response['status']) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = Session::get('user.id');
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->axb_number = $response['data']['axb_number'];
            $CallHistory->called_number = $response['data']['mobile'];
            $CallHistory->save();
        }
        return json($response);
    }
}
