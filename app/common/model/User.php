<?php


namespace app\common\model;


class User extends \think\Model
{
    public function getPrevtimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function getLogintimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
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
}
