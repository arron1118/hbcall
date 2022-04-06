<?php

namespace app\api\controller;

use app\common\controller\ApiController;

class Error extends ApiController
{
    public function __call($name, $arguments)
    {
        $this->returnData['msg'] = '请求错误';
        $this->returnApiData();
    }
}
