<?php

namespace app\common\model;

use app\company\model\Company;

class Customer extends \think\Model
{
    public function getCreatetimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getLastCalltimeAttr($value)
    {
        return $value ? (date('d', $value) === date('d', time()) ?
            date('H:i:s', $value) :
            (date('Y', $value) === date('Y', time()) ? date('m-d H:i:s', $value)
            : date($this->getDateFormat(), $value))
        ) : '-';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
