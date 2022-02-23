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
        return $this->belongsTo(\app\company\model\Company::class, 'company_id');
    }

    public function userXnumber()
    {
        return $this->hasOne(UserXnumber::class, 'user_id')->bind(['xnumber']);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
}
