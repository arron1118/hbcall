<?php
/**
 * copyright@Administrator
 * 2023/8/21 0021 10:47
 * email:arron1118@icloud.com
 */

namespace app\common\model;

use think\model\concern\SoftDelete;

class UserSigninLogs extends \think\Model
{
    use SoftDelete;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
