<?php


namespace app\company\controller;


class Payment extends \app\common\controller\CompanyController
{

    public function index()
    {
        return $this->view->fetch();
    }

    public function pay()
    {
        return $this->view->fetch();
    }
}
