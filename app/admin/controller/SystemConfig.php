<?php
/**
 * copyright@Administrator
 * 2023/11/18 0018 15:03
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

class SystemConfig extends \app\common\controller\AdminController
{

    protected function initialize()
    {
        parent::initialize();

        $this->model = new \app\admin\model\SystemConfig();
    }

    public function index()
    {

        return $this->view->fetch();
    }

}
