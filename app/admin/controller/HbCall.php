<?php

namespace app\admin\controller;

use app\common\model\User;
use app\common\traits\CallHistoryTrait;

class HbCall extends \app\common\controller\AdminController
{
    use CallHistoryTrait;

    public function getUserList($company_id = 0)
    {
        if ($company_id > 0) {
            $userList = User::field('id, username')
                ->where('company_id', $company_id)
                ->order('id desc, logintime desc')
                ->select();
            $this->returnData['code'] = 1;
            $this->returnData['data'] = $userList;
            $this->returnData['msg'] = 'success';

            return json($this->returnData);
        }

        return json($this->returnData);
    }
}
