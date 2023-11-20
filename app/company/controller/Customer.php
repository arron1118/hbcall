<?php

namespace app\company\controller;

use app\common\model\Customer as CustomerModel;
use app\common\traits\CustomerTrait;
use think\facade\Db;
use think\facade\Event;

class Customer extends \app\common\controller\CompanyController
{
    use CustomerTrait;

    /**
     * 分配客户/人才
     * @return \think\response\Json
     */
    public function distribution()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '分配失败';
            $ids = trim($this->request->param('ids', ''), ',');
            $userId = $this->request->param('user_id', 0);
            $user = \app\common\model\User::find($userId);

            Event::trigger('Customer', $user);

            $check = $this->checkCustomerNumForUser($user);
            if ($check) {
                $this->returnData['msg'] = '已超出该用户的数量限制';
                return json($this->returnData);
            }

            $customers = CustomerModel::withTrashed()->whereIn('id', $ids)->update([
                'recycle' => 0,
                'user_id' => $userId,
                'distribution_time' => time(),
                'distribution_count' => Db::raw('distribution_count+1'),
                'delete_time' => null,
            ]);
            if ($customers) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '分配成功';
                $this->returnData['data']['check'] = $check;
                $this->returnData['data']['customer'] = $customers;
            }
        }

        return json($this->returnData);
    }

    /**
     * 迁移数据
     * @return \think\response\Json
     */
    public function migrate()
    {
        if ($this->request->isPost()) {
            $ids = trim($this->request->param('ids', ''), ',');
            $type = $this->request->param('type/d', 0);

            if (!$type) {
                $this->returnData['msg'] = '参数不正确';
                return json($this->returnData);
            }

            $type = $type === 1 ? 2 : 1;

            $customers = CustomerModel::where([
                'company_id' => $this->userInfo->id,
            ])
                ->whereIn('id', $ids)
                ->update(['type' => $type]);

            $this->returnData['data'] = $customers;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '迁移成功';
        }

        return json($this->returnData);
    }
}
