<?php


namespace app\portal\controller;


class Index extends \app\common\controller\PortalController
{
    public function index()
    {
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function product()
    {
        $this->title = '产品';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function cooperate()
    {
        $this->title = '合作伙伴';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function solution()
    {
        $this->title = '方案';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function case()
    {
        $this->title = '案例';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function about()
    {
        $this->title = '关于我们';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function buy()
    {
        $this->title = '购买&试用';
        $this->view->assign('title', $this->title);
        return $this->view->fetch();
    }

    public function test()
    {
        $this->title = '测试页';
        $this->view->assign('title', $this->title);
        return 'Test';
    }
}
