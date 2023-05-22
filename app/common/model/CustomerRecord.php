<?php

namespace app\common\model;

use \think\Model;
use think\model\concern\SoftDelete;

class CustomerRecord extends Model
{
    use SoftDelete;

    public function getNextCallTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
