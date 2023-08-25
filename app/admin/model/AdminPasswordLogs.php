<?php
/**
 * copyright@Administrator
 * 2023/8/24 0024 11:10
 * email:arron1118@icloud.com
 */

namespace app\admin\model;

use app\common\model\CommonModel;

class AdminPasswordLogs extends CommonModel
{

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
