<?php


namespace app\admin\model;

use app\common\model\CallTypeLogs;
use app\common\model\CommonModel;

class Admin extends CommonModel
{

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    public function passwordLogs()
    {
        return $this->hasMany(AdminPasswordLogs::class);
    }

    public function signinLogs()
    {
        return $this->hasMany(AdminSigninLogs::class);
    }

    public function callTypeLogs()
    {
        return $this->hasMany(CallTypeLogs::class);
    }

    protected function getStatusList()
    {
        return [0 => '禁止登录', 1 => '正常'];
    }
}
