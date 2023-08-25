<?php

namespace app\common\model;

use think\db\Query;

class Company extends CommonModel
{

    public function getPrevtimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getLogintimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getTestEndtimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '';
    }

    public function getContractStartDatetimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '';
    }

    public function getContractEndDatetimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    public function getCallStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['call_status']];
    }

    public function getIsTestTextAttr($value, $data)
    {
        return $this->getTestList()[$data['is_test']];
    }

    public function getCallTypeTextAttr($value, $data)
    {
        return $this->callTypeList()[$data['call_type']];
    }

    public function getStatusList()
    {
        return ['-1' => '全部', '禁止', '正常'];
    }

    public function getCallStatusList()
    {
        return ['-1' => '全部', '禁止', '正常'];
    }

    public function getTestList()
    {
        return ['-1' => '全部', '否', '是'];
    }

    public function callTypeList()
    {
        return [1 => 'AXB线路', 2 => '三网通回拨', 3 => '电信回拨', 4 => '移动回拨', 5 => '新三网回拨线路'];
    }

    public function getCallTypeList()
    {
        return [1 => 'axb', 2 => 'callback', 3 => 'DxCallBack', 4 => 'YDCallBack', 5 => 'WxCallback'];
    }

    public function getCompanyList()
    {
        return $this->with(['user' => function (Query $query) {
            $query->field('id, company_id, realname');
        }])->field('id, username, corporation')
            ->order('id desc')
            ->select();
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function expense()
    {
        return $this->hasMany(Expense::class);
    }

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function companyXnumber()
    {
        return $this->hasOne(CompanyXnumber::class)->bind(['number']);
    }

    public function customerPhoneRecord()
    {
        return $this->hasMany(CustomerPhoneRecord::class);
    }

    public function passwordLogs()
    {
        return $this->hasMany(CompanyPasswordLogs::class);
    }

    public function signinLogs()
    {
        return $this->hasMany(CompanySigninLogs::class);
    }

    public static function onAfterDelete($company)
    {
        CompanyXnumber::destroy(function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });
    }
}
