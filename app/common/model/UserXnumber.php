<?php


namespace app\common\model;

use \think\Model;

class UserXnumber extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function numberStore()
    {
        return $this->belongsTo(NumberStore::class)->bind(['number']);
    }
}
