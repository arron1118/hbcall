<?php

namespace app\company\controller;

use app\common\model\CustomerRecord as RecordModel;
use app\common\traits\CustomerRecordTrait;

class CustomerRecord extends \app\common\controller\CompanyController
{
    use CustomerRecordTrait;
}
