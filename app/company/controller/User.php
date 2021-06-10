<?php


namespace app\company\controller;


class User extends \app\common\controller\CompanyController
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
