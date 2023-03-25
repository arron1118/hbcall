<?php

namespace app\company\controller;

use app\common\controller\CompanyController;
use think\facade\Db;
use app\common\traits\ReportTrait;

class Index extends CompanyController
{
    use ReportTrait;

    public function index()
    {
        return $this->view->fetch('common@index/index');
    }
}
