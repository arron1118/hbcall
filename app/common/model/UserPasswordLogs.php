<?php
/**
 * copyright@Administrator
 * 2023/8/24 0024 11:09
 * email:arron1118@icloud.com
 */

namespace app\common\model;

class UserPasswordLogs extends CommonModel
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
