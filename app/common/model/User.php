<?php


namespace app\common\model;


class User extends \think\Model
{
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_at';

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
        return date($this->getDateFormat(), $value);
    }

    protected function getStatusList()
    {
        return [0 => '禁止登录', 1 => '正常'];
    }

    public function getIsTestAttr($value)
    {
        return $this->getTestList()[$value];
    }

    protected function getTestList()
    {
        return [0 => '否', 1 => '是'];
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
