<?php

namespace app\common\model;

use app\company\model\Company;
use app\common\model\CustomerRecord;

class Customer extends \think\Model
{
    public function getCreatetimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getLastCalltimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getCateAttr($value)
    {
        return $this->getCateList()[$value];
    }

    public function getCateList()
    {
        return [
            '-1' => '全部',
            '意向客户',
            '重点客户',
            '成交客户',
            '无效客户',
        ];
    }

    public static function onAfterDelete($customer)
    {
        CustomerRecord::destroy(function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->bind(['username']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function record()
    {
        return $this->hasMany(CustomerRecord::class);
    }
}
