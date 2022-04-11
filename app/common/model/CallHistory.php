<?php


namespace app\common\model;


use think\Model;
use think\model\relation\BelongsTo;

class CallHistory extends \think\Model
{
    protected $autoWriteTimestamp = true;

    protected $createTime = 'createtime';

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

    public function getCalledNumberAttr($value)
    {
        return substr_replace($value, '****', 3, 4);
    }

    public function getStarttimeAttr($value)
    {
        return $value ? (date('d', $value) === date('d', time()) ?
            date('H:i:s', $value) :
            (date('Y', $value) === date('Y', time()) ? date('m-d H:i:s', $value)
                : date($this->getDateFormat(), $value))
        ) : '-';
    }

    public function getReleasetimeAttr($value)
    {
        return $value ? (date('d', $value) === date('d', time()) ?
            date('H:i:s', $value) :
            (date('Y', $value) === date('Y', time()) ? date('m-d H:i:s', $value)
                : date($this->getDateFormat(), $value))
        ) : '-';
    }

    public function getCreatetimeAttr($value)
    {
        return $value ? (date('d', $value) === date('d', time()) ?
            date('H:i:s', $value) :
            (date('Y', $value) === date('Y', time()) ? date('m-d H:i:s', $value)
                : date($this->getDateFormat(), $value))
        ) : '-';
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
        return $this->hasOne(Expense::class)->bind(['cost', 'duration']);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
