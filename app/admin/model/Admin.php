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

}
