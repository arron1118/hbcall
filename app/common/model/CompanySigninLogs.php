<?php
/**
 * copyright@Administrator
 * 2023/8/21 0021 10:47
 * email:arron1118@icloud.com
 */

namespace app\common\model;

use think\model\concern\SoftDelete;

class CompanySigninLogs extends \think\Model
{
    use SoftDelete;

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

}
