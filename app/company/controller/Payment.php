<?php


namespace app\company\controller;

use app\api\library\WxPayConfig;

class Payment extends \app\common\controller\CompanyController
{

    public function index()
    {
        $wxpay = new WxPayConfig();
        dump($wxpay->GetSignType());
        return $this->view->fetch();
    }

    public function pay()
    {
        return $this->view->fetch();
    }
}
