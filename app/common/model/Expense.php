<?php


namespace app\common\model;


use app\company\model\Company;

class Expense extends \think\Model
{

    public function getCreatetimeAttr($value)
    {
        return getDateFormatInfo($value);
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
