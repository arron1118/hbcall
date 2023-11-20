<?php

namespace app\common\model;

class Customer extends CommonModel
{

    protected $typeList = [
        '全部',
        '客户',
        '人才',
    ];

    public function getLastCalltimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getDeleteTimeAttr($value)
    {
        return getDateFormatInfo($value);
    }

    public function getPhoneAttr($value)
    {
        return validateMobile($value) ? substr_replace($value, '****', 3, 4) : $value;
    }

    public function getCateTextAttr($value, $data)
    {
        return $this->getCateList($data['type'])[$data['cate']];
    }

    public function getTypeTextAttr($value, $data)
    {
        return $this->typeList[$data['type']];
    }

    public function getCateList($type = 1)
    {
        $type = $this->typeList[$type];
        return [
            '-1' => '全部',
            '意向' . $type,
            '重点' . $type,
            '成交' . $type,
            '无效' . $type,
        ];
    }

    public function getTypeList()
    {
        return $this->typeList;
    }

    public function getSearchItem($type = 1)
    {
        $searchItem = [
            'title' => '名称',
            'phone' => '联系电话',
            'comment' => '备注',
        ];

        if ($type === 2) {
            $searchItem['professional'] = '专业';
            $searchItem['certificate'] = '证书类型';
        }

        return $searchItem;
    }

    public function user()
    {
        return $this->belongsTo(User::class)->bind(['realname']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->bind(['corporation']);
    }

    public function callHistory()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function record()
    {
        return $this->hasMany(CustomerRecord::class);
    }

    public function customerPhoneRecord()
    {
        return $this->hasMany(CustomerPhoneRecord::class);
    }
}
