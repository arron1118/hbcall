<?php

namespace app\common\model;

class CallHistory extends CommonModel
{

    public function getCallDurationAttr($value)
    {
        $minute = floor($value / 60);
        $second = $value % 60;

        if ($minute < 10) {
            $minute = '0' . $minute;
        }

        if ($second < 10) {
            $second = '0' . $second;
        }

        return $minute . ':' . $second;
    }

    public function getStarttimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getCalledNumberAttr($value)
    {
        return substr_replace($value, '****', 3, 4);
    }

    public function getReleasetimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    public function getStatusList()
    {
        return ['未同步', '已同步'];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->bind(['user_username' => 'username', 'loginip']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->bind(['corporation']);
    }

    public function expense()
    {
        return $this->hasOne(Expense::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function callType()
    {
        return $this->belongsTo(CallType::class);
    }
}
