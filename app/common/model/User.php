<?php


namespace app\common\model;

class User extends CommonModel
{
    protected $json = [
        'region_ids',
    ];

    protected $jsonAssoc = true;

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    public function getTestEndtimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '';
    }

    public function getStatusList()
    {
        return ['-1' => '全部', '禁止', '正常'];
    }

    public function getIsTestTextAttr($value, $data)
    {
        return $this->getTestList()[$data['is_test']];
    }

    public function getTestList()
    {
        return ['-1' => '全部', '否', '是'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function userXnumber()
    {
        return $this->hasOne(UserXnumber::class)->bind(['number']);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function expense()
    {
        return $this->hasMany(Expense::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }

    public function customerPhoneRecord()
    {
        return $this->hasMany(CustomerPhoneRecord::class);
    }

    public function passwordLogs()
    {
        return $this->hasMany(UserPasswordLogs::class);
    }

    public function signinLogs()
    {
        return $this->hasMany(UserSigninLogs::class);
    }

    public static function onAfterDelete($user): void
    {
        UserXnumber::destroy(function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }
}
