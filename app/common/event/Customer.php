<?php
declare (strict_types = 1);

namespace app\common\event;

use app\common\model\User;
use app\common\model\Customer as CustomerModel;

class Customer
{
    public function handle(User $user)
    {
        // 1 客户 2 人才
        foreach ([1, 2] as $value) {
            $number = 0;
            $keep_time = 0;
            if ($value === 1) {
                $number = (int) $user->customer_num;
                $keep_time = $user->customer_keep_time;
            } else {
                $number = (int) $user->talent_num;
                $keep_time = $user->talent_keep_time;
            }

            $where = [
                ['cate', 'in', [0, 3]], // 意向客户 无效客户
                ['type', '=', $value],
                ['user_id', '=', $user->id]
            ];
            // 保存时长
            if ($keep_time) {
                $time = time() - $keep_time * 86400;
                $map = $where;
                $map[] = ['distribution_time', '<', $time];
                $this->delete($map);
            }

            // 数量限制
            if ($number) {
                $customer = CustomerModel::where($where)
                    ->order('id', 'desc')
                    ->limit((int) $number, 1)
                    ->column('id');

                if ($customer) {
                    $map = $where;
                    $map[] = ['id', '<=', $customer[0]];
                    $this->delete($map);
                }
            }
        }
    }

    /**
     * 回收数据
     * @param $where
     * @return bool
     */
    protected function delete($where)
    {
        return CustomerModel::where($where)->save([
//            'user_id' => 0,
            'distribution_time' => null,
            'recycle' => 1,
        ]);
    }
}
