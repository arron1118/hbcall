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

}
