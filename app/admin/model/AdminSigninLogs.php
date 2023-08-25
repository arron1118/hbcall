<?php
/**
 * copyright@Administrator
 * 2023/8/21 0021 10:47
 * email:arron1118@icloud.com
 */

namespace app\admin\model;

use app\common\model\CommonModel;

class AdminSigninLogs extends CommonModel
{

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

}
