<?php

namespace app\admin\controller;

use app\common\model\Customer as CustomerModel;
use app\common\traits\CustomerTrait;

class Customer extends \app\common\controller\AdminController
{

    use CustomerTrait;

    protected $cateList = [];

    public function initialize()
    {
        parent::initialize();

        $this->cateList = (new CustomerModel())->getCateList();
        $this->view->assign('cateList', $this->cateList);
    }
}
