<?php
/**
 * copyright@Administrator
 * 2023/9/27 0027 15:35
 * email:arron1118@icloud.com
 */

namespace app\common\model;

use app\admin\model\Admin;

class CallTypeLogs extends CommonModel
{

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function callType()
    {
        return $this->belongsTo(CallType::class);
    }

}
