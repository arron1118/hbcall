<?php


namespace app\portal\controller;


class Detail extends \app\common\controller\PortalController
{

    public function detailIndex()
    {
        return $this->view->fetch();
    }
}
