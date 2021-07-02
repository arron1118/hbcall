<?php

namespace app\api\controller;

use app\common\controller\ApiController;

class User extends ApiController
{
    public function index()
    {
        dump($this->getUserInfo());
        dump($this->isLogin());
    }
}
