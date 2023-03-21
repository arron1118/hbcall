<?php


namespace app\common\model;

use \think\Model;

class User extends Model
{
    public function getPrevtimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getLogintimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    public function getTestEndtimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '';
    }

    public function getStatusList()
    {
        return ['-1' => '全部', '禁止', '正常'];
    }

    public function getIsTestAttr($value)
    {
        return $this->getTestList()[$value];
    }

    public function getTestList()
    {
        return ['-1' => '全部', '否', '是'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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

    public function customerPhoneRecord()
    {
        return $this->hasMany(CustomerPhoneRecord::class);
    }
}
