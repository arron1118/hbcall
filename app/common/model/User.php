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
