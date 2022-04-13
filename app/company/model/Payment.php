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
        if (!$value) {
            return '-';
        }
        return date($this->getDateFormat(), $value);
    }

    public function getPayTypeAttr($value)
    {
        return $this->getPayTypeList()[$value];
    }

    public function getPayTypeList()
    {
        return ['所有', '微信', '支付宝', '手动充值'];
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    public function getStatusList()
    {
        return ['-1' => '所有', '未支付', '已支付', '已关闭'];
    }

    public function company()
    {
        return $this->belongsTo(\app\company\model\Company::class)->bind(['username', 'corporation']);
    }

}
