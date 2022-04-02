<?php

namespace app\common\model;

class CustomerRecord extends \think\Model
{
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_at';

    public function getCreateAtAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
