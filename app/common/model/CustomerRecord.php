<?php

namespace app\common\model;

use \think\Model;

class CustomerRecord extends Model
{
    public function getNextCallTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
