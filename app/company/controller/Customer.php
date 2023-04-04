<?php

namespace app\company\controller;

use app\common\model\Customer as CustomerModel;
use app\common\traits\CustomerTrait;

class Customer extends \app\common\controller\CompanyController
{
    use CustomerTrait;
}
