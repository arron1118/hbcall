<?php


namespace app\company\model;


use app\common\model\CallHistory;
use app\common\model\CompanyXnumber;
use app\common\model\Customer;
use app\common\model\User;
use app\common\model\Expense;

class Company extends \think\Model
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

    public function getTestEndtimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    public function getIsTestAttr($value)
    {
        return $this->getTestList()[$value];
    }

    public function getStatusList()
    {
        return ['-1' => '全部', '禁止', '正常'];
    }

    public function getTestList()
    {
        return ['-1' => '全部', '否', '是'];
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
