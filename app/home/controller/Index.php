<?php
namespace app\home\controller;

use app\common\controller\HomeController;

class Index extends HomeController
{
    public function index()
    {
        $this->view->assign('title', 'lqpbd');
        return $this->view->fetch();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return $this->view->fetch();
    }

    public function test()
    {
        return $this->view->fetch();
    }
}
