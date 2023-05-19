<?php
declare (strict_types = 1);

namespace app\common\event;

use app\common\model\Company;
use app\common\model\Customer as CustomerModel;

class Customer
{
    public function handle(Company $company)
    {
        // 1 客户 2 人才
        foreach ([1, 2] as $value) {
            $flag = true;
            $number = 0;
            $keep_time = 0;
            if ($value === 1) {
                $number = $company->customer_num;
                $keep_time = $company->customer_keep_time;
            } else {
                if (!$company->talent_on) {
                    $flag = false;
                } else {
                    $number = $company->talent_num;
                    $keep_time = $company->talent_keep_time;
                }
            }

            if ($flag) {
                $where = [
                    ['cate', 'in', [0, 3]],
                    ['type', '=', $value],
                    ['company_id', '=', $company->id]
                ];
                // 保存时长
                if ($keep_time) {
                    $time = time() - $keep_time * 86400;
                    $map = $where;
                    $map[] = ['create_time', '<', $time];
                    $this->delete($map);
                }

                // 数量限制
                if ($number) {
                    $customer = CustomerModel::where($where)->order('id', 'desc')
                        ->limit($number, 1)
                        ->column('id');

                    if ($customer) {
                        $map = $where;
                        $map[] = ['id', '<=', $customer[0]];
                        $this->delete($map);
                    }
                }
            }
        }
    }

    protected function delete($where)
    {
        return CustomerModel::where($where)->save([
            'delete_time' => time(),
        ]);
    }
}
