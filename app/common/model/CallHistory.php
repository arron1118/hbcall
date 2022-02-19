<?php


namespace app\common\model;


use think\model\relation\BelongsTo;

class CallHistory extends \think\Model
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
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getReleasetimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function getCreatetimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }

    public function user()
    {
        return $this->belongsTo(\app\common\model\User::class)->bind(['user_username' => 'username', 'loginip']);
    }

    public function company()
    {
        return $this->belongsTo(\app\company\model\Company::class)->bind(['corporation']);
    }

    public function expense()
    {
        return $this->hasOne(Expense::class)->bind(['cost']);
    }
}
