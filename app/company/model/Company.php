<?php


namespace app\company\model;


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

    public function payments()
    {
        return $this->hasMany(\app\company\Model\Payment::class)->bind(['corporation']);
    }

    public function users()
    {
        return $this->hasMany(\app\common\model\User::class)->bind(['corporation']);
    }
}
