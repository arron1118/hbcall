<?php


namespace app\common\model;

class UserXnumber extends CommonModel
{
    protected $deleteTime = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function numberStore()
    {
        return $this->belongsTo(NumberStore::class)->bind(['number']);
    }
}
