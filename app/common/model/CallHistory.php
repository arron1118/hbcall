<?php

namespace app\common\model;

use think\Model;

class CallHistory extends Model
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

    public function getReleasetimeAttr($value)
    {
        return getDateFormatInfo($value);
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
        return $this->hasOne(Expense::class)->bind(['cost', 'duration']);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
