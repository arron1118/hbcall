<?php

namespace app\api\controller;

use app\common\controller\ApiController;

class Error extends ApiController
{
    public function __call($method, $args)
    {
        $this->returnData['msg'] = '错误的请求[控制器不存在]：' . $method;
        $this->returnApiData();
    }
}
