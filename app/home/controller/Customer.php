<?php

namespace app\home\controller;

use app\common\model\Customer as CustomerModel;
use app\common\traits\CustomerTrait;

class Customer extends \app\common\controller\HomeController
{
    use CustomerTrait;

}
