<?php


namespace app\common\model;

use \think\Model;

class Payment extends Model
{

    public function getPayTimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getPayTypeTextAttr($value, $data)
    {
        return $this->getPayTypeList()[$data['pay_type']];
    }

    public function getPayTypeList()
    {
        return ['全部', '微信', '支付宝', '手动充值'];
    }

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    public function getStatusList()
    {
        return ['-1' => '全部', '未支付', '已支付', '已关闭'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->bind(['username', 'corporation']);
    }

}
