<?php


namespace app\common\model;


class User extends \think\Model
{
    public function getPrevtimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getLogintimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return [0 => '禁止登录', 1 => '正常'];
    }

    public function company()
    {
        return $this->belongsTo(\app\company\model\Company::class);
    }

    public function userXnumber()
    {
        return $this->hasOne(UserXnumber::class)->bind(['number']);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function expense()
    {
        return $this->hasMany(Expense::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
}
