<?php

namespace app\home\controller;

use app\common\model\Customer as CustomerModel;

class Customer extends \app\common\controller\HomeController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function getCustomerList()
    {
        if ($this->request->isPost()) {
            $where = [
                'user_id' => $this->userInfo->id
            ];

            $res = CustomerModel::field('id, title, phone, called_count, last_calltime')
                ->where($where)
                ->order('called_count')
                ->order('id', 'desc')
                ->select();

            $this->returnData['data'] = $res;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function CustomerRecordList()
    {
        $this->view->assign('customerId', $this->request->param('customerId'));
        return $this->view->fetch();
    }
}
