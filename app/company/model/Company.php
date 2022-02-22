<?php


namespace app\company\model;


use app\common\model\Customer;
use app\common\model\User;

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

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return [0 => '禁止登录', 1 => '正常'];
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function axbNumbers()
    {
        return $this->hasMany(User::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
}
