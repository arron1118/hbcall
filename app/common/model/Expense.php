<?php


namespace app\common\model;


use \think\Model;
use think\model\concern\SoftDelete;

class Expense extends Model
{
    use SoftDelete;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
