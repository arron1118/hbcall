<?php


namespace app\admin\model;


class Admin extends \think\Model
{

    public function getPrevtimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function getLogintimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    protected function getStatusList()
    {
        return [0 => '禁止登录', 1 => '正常'];
    }
}
