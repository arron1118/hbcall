<?php


namespace app\common\model;


class NumberStore extends \think\Model
{

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return [0 => '未使用', 1 => '已使用', 9 => '不可用'];
    }
}
