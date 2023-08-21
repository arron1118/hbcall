<?php
/**
 * copyright@Administrator
 * 2023/8/21 0021 10:47
 * email:arron1118@icloud.com
 */

namespace app\common\model;

use app\admin\model\Admin;
use think\model\concern\SoftDelete;

class AdminSigninLogs extends \think\Model
{
    use SoftDelete;

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

}
