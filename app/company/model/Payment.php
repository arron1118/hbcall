<?php


namespace app\company\model;


class Payment extends \think\Model
{

    public function getCreateTimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

    public function getPayTimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }



}
