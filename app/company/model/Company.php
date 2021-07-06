<?php


namespace app\company\model;


class Company extends \think\Model
{
    public function getPrevtimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function getLogintimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function payments()
    {
        return $this->hasMany(\app\company\Model\Payment::class);
    }

    public function users()
    {
        return $this->hasMany(\app\common\model\User::class);
    }
}
