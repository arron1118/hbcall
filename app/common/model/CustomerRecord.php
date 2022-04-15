<?php

namespace app\common\model;

class CustomerRecord extends \think\Model
{
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_at';

    public function getCreateAtAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getNextCallTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
