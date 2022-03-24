<?php


namespace app\common\model;


use app\company\model\Company;

class NumberStore extends \think\Model
{

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return [0 => '未使用', 1 => '已使用', 9 => '不可用'];
    }

    public function company()
    {
        return $this->belongsTo(\app\company\model\Company::class);
    }

    public function user()
    {
        return $this->belongsTo(\app\common\model\User::class)->bind(['number']);
    }

    public function companyXnumber()
    {
        return $this->hasMany(CompanyXnumber::class);
    }

    public function userXnumber()
    {
        return $this->hasMany(UserXnumber::class);
    }
}
