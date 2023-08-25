<?php
/**
 * copyright@Administrator
 * 2023/8/24 0024 11:10
 * email:arron1118@icloud.com
 */

namespace app\common\model;

class CompanyPasswordLogs extends CommonModel
{

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


}
