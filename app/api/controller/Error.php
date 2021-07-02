<?php

namespace app\api\controller;

use app\common\controller\ApiController;

class Error extends ApiController
{
    public function __call($name, $arguments)
    {
        return 'error request!';
    }
}
