<?php
namespace app\home\controller;

use app\common\controller\HomeController;
use app\api\controller\HbCall;
use Curl\Curl;

class Index extends HomeController
{
    public function index()
    {
        $this->view->assign('title', 'lqpbd');
        return $this->view->fetch();
    }

    public function calltable($name = 'ThinkPHP6')
    {
        return $this->view->fetch();
    }
    public function calling()
    {
        $curl = new Curl();
        $result = $curl->post('http://call.hbosw.net/API/axbCallApi.aspx', [
            'mobile' => '19868115646',
            'axb_number' => '18426190532'
        ]);
        dump(json_decode($curl->response, true));
        $this->view->assign('result', $result);
        /*$result = curl_request('http://call.hbosw.net/API/axbCallApi.aspx', true, [
            'mobile' => '19868115646',
            'axb_number' => '18426190532'
        ]);
        dump($result);*/
        return $this->view->fetch();
    }
    public function login()
    {
        return $this->view->fetch();
    }

}
