<?php
namespace app\company\controller;

use app\common\controller\HomeController;

class Index extends HomeController
{
    public function index()
    {
        $this->view->assign('title', 'lqpbd');
        return $this->view->fetch();
    }

    public function open()
    {
        return $this->view->fetch();
    }
    public function calling()
    {
        return $this->view->fetch();
    }
    public function calltable()
    {
        return $this->view->fetch();
    }
    public function add()
    {
        return $this->view->fetch();
    }
    public function login()
    {
        return $this->view->fetch();
    }

}
