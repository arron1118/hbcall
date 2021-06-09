<?php


namespace app\home\controller;


use Curl\Curl;

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
            'mobile' => '19868115646',
            'axb_number' => $this->axb_number
        ]);
        $response = json_decode($curl->response, true);
        return json($response);
    }
}
