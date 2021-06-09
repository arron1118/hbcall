<?php

namespace app\home\controller;

use app\common\controller\HomeController;

class Index extends HomeController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function login()
    {
        return $this->view->fetch();
    }

}
