<?php

namespace app\common\model;

use \think\Model;

class CompanyXnumber extends Model
{
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function numberStore()
    {
        return $this->belongsTo(NumberStore::class)->bind(['number']);
    }
}
