<?php


namespace app\portal\controller;


class Detail extends \app\common\controller\PortalController
{

    public function index()
    {
        return $this->view->fetch();
    }
}
