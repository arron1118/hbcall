<?php


namespace app\portal\controller;


class Index extends \app\common\controller\PortalController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function product()
    {
        return $this->view->fetch();
    }
    public function cooperate()
    {
        return $this->view->fetch();
    }
    public function solution()
    {
        return $this->view->fetch();
    }
    public function case()
    {
        return $this->view->fetch();
    }
    public function news()
    {
        return $this->view->fetch();
    }
    public function about()
    {
        return $this->view->fetch();
    }
    public function buy()
    {
        return $this->view->fetch();
    }
}
