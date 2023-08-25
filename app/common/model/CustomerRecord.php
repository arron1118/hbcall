<?php

namespace app\common\model;

class CustomerRecord extends CommonModel
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
