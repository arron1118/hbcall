<?php


namespace app\company\model;


use app\common\model\CallHistory;
use app\common\model\CompanyXnumber;
use app\common\model\Customer;
use app\common\model\User;
use app\common\model\Expense;

class Company extends \think\Model
{
    public function getPrevtimeAttr($value)
    {
        return $value > 0 ? date($this->getDateFormat(), $value) : '-';
    }

    public function getLogintimeAttr($value)
    {
        return $value > 0 ? date($this->getDateFormat(), $value) : '-';
    }

    public function getTestEndtimeAttr($value)
    {
        return $value > 0 ? date($this->getDateFormat(), $value) : '';
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    public function getIsTestAttr($value)
    {
        return $this->getTestList()[$value];
    }

    protected function getStatusList()
    {
        return [0 => '禁止', 1 => '正常'];
    }

    protected function getTestList()
    {
        return [0 => '否', 1 => '是'];
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function expense()
    {
        return $this->hasMany(Expense::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function companyXnumber()
    {
        return $this->hasOne(CompanyXnumber::class)->bind(['number']);
    }
}
