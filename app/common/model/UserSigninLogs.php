<?php
/**
 * copyright@Administrator
 * 2023/8/21 0021 10:47
 * email:arron1118@icloud.com
 */

namespace app\common\model;

class UserSigninLogs extends CommonModel
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getDeviceTypeTextAttr()
    {

    }
}
