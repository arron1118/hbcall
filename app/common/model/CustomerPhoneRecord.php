<?php
declare (strict_types = 1);

namespace app\common\model;

/**
 * CustomerPhoneRecord
 */
class CustomerPhoneRecord extends CommonModel
{

    public function getCustomerPhoneAttr($value)
    {
        return substr_replace($value, '****', 3, 4);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
