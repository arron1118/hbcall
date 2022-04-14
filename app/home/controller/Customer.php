<?php

namespace app\home\controller;

use app\common\model\Customer as CustomerModel;

class Customer extends \app\common\controller\HomeController
{
    public function initialize()
    {
        parent::initialize();

        $this->view->assign('cateList', (new CustomerModel())->getCateList());
    }

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
            $cate = (int) $this->request->param('cate', -1);

            $where = [
                ['user_id', '=', $this->userInfo->id]
            ];

            if ($cate !== -1) {
                $where[] = ['cate', '=', $cate];
            }

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

    public function changeCate()
    {
        if ($this->request->isPost()) {
            $ids = trim($this->request->param('ids', ''), ',');
            $cateId = $this->request->param('cate', 0);
            $customers = CustomerModel::whereIn('id', $ids)->update(['cate' => $cateId]);
            $this->returnData['data'] = $customers;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function CustomerRecordList()
    {
        $this->view->assign('customerId', $this->request->param('customerId'));
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            return json($this->returnData);
        }
        return $this->view->fetch();
    }

    public function edit()
    {
        $customerId = (int) $this->request->param('customerId', 0);
        $customer = CustomerModel::find($customerId);

        if (!$customer) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            return json($this->returnData);
        }

        $this->view->assign('customer', $customer);

        return $this->view->fetch();
    }
}
