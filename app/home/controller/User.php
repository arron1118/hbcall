<?php


namespace app\home\controller;


class User extends \app\common\controller\HomeController
{

    public function profile()
    {
        return $this->view->fetch();
    }

    public function resetPassword()
    {
        return $this->view->fetch();
    }
}
