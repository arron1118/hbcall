<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\common\traits\ReportTrait;
use app\common\model\Company;

class Index extends AdminController
{
    use ReportTrait;

    public function index()
    {
        return $this->view->fetch('common@index/index');
    }

    public function getCallList()
    {
        if ($this->request->isGet()) {
            $this->returnData['code'] = 1;
            return json($this->returnData);
        }

        return json($this->returnData);
    }

}
