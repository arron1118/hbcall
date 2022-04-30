<?php


namespace app\admin\controller;

use app\common\traits\PaymentTrait;

class Payment extends \app\common\controller\AdminController
{
    use PaymentTrait;

    public function initialize() {
        parent::initialize();

        $this->model = new \app\common\model\Payment();
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->view->assign('payTypeList', $this->model->getPayTypeList());
    }
}
