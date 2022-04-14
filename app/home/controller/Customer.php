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
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $title = trim($this->request->param('title', ''));
            $phone = trim($this->request->param('phone', ''));

            $where = [
                ['user_id', '=', $this->userInfo->id]
            ];

            if ($title) {
                $where[] = ['title', 'like', '%' . $title . '%'];
            }

            if ($phone) {
                $where[] = ['phone', 'like', '%' . $phone . '%'];
            }

            $total = CustomerModel::where($where)->count();

            $res = CustomerModel::withCount(['record'])
                ->where($where)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->returnData['data'] = $res;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['total'] = $total;

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
