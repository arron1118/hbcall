<?php


namespace app\common\model;


class UserXnumber extends \think\Model
{

    public function user()
    {
        return $this->belongsTo(User::class)->bind(['xnumber']);
    }

}
