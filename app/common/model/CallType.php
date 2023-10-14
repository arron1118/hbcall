<?php
/**
 * copyright@Administrator
 * 2023/10/7 0007 17:17
 * email:arron1118@icloud.com
 */

namespace app\common\model;

class CallType extends CommonModel
{

    public function company()
    {
        return $this->belongsToMany(Company::class);
    }
}
