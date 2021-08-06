<?php


namespace app\common\model;


class Expense extends \think\Model
{

    public function getCreatetimeAttr($value)
    {
        return date($this->getDateFormat(), $value);
    }

}
