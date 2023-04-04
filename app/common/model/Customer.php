<?php

namespace app\common\model;

use \think\Model;

class Customer extends Model
{
    public function getLastCalltimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getPhoneAttr($value)
    {
        return substr_replace($value, '****', 3, 4);
    }

    public function getCateTextAttr($value, $data)
    {
        return $this->getCateList()[$data['cate']];
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
        return $this->belongsTo(User::class)->bind(['realname']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->bind(['corporation']);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function record()
    {
        return $this->hasMany(CustomerRecord::class);
    }

    public function customerPhoneRecord()
    {
        return $this->hasMany(CustomerPhoneRecord::class);
    }
}
