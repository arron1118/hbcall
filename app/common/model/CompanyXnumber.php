<?php

namespace app\common\model;

use app\company\model\Company;

class CompanyXnumber extends \think\Model
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
