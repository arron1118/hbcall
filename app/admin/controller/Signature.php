<?php
/**
 * copyright@Administrator
 * 2024/3/19 0019 11:56
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

use app\common\controller\AdminController;

class Signature extends AdminController
{
    public function index()
    {
        return $this->view->fetch();
    }
}
