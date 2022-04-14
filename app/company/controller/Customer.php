<?php

namespace app\company\controller;

use app\common\model\Customer as CustomerModel;
use think\db\exception\DbException;

class Customer extends \app\common\controller\CompanyController
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

    public function importExcel()
    {
        if ($this->request->isPost()) {
            $file = request()->file('file');
            $data = $this->readExcel($file);
            try {
                $res = (new \app\common\model\Customer())->saveAll($data);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('The import was successful');
                $this->returnData['data'] = $res;

                return json($this->returnData);
            } catch (DbException $dbException) {
                $this->returnData['code'] = $dbException->getCode();
                $this->returnData['msg'] = $dbException->getMessage();
                $this->returnData['data'] = $dbException->getData();

                return json($this->returnData);
            }
        }

        return json($this->returnData);
    }

    protected function readExcel($file)
    {
        return readExcel($file, [
            'createtime' => time(),
            'company_id' => $this->userInfo->id,
            'user_id' => 0,
        ]);
    }

    public function getCustomerList()
    {
        if ($this->request->isPost()) {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $title = trim($this->request->param('title', ''));
            $phone = trim($this->request->param('phone', ''));
            $status = (int) $this->request->param('status', -1);
            $cate = (int) $this->request->param('cate', -1);

            $where = [
                ['company_id', '=', $this->userInfo->id]
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

            if ($status === 0) {
                $where[] = ['user_id', '=', 0];
            } elseif ($status === 1) {
                $where[] = ['user_id', '>', 0];
            }

            $total = CustomerModel::where($where)->count();

            $res = CustomerModel::with(['user'])
                ->withCount(['record'])
                ->where($where)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->returnData['data'] = $res->hidden(['user']);
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['total'] = $total;

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function distribution()
    {
        if ($this->request->isPost()) {
            $ids = trim($this->request->param('ids', ''), ',');
            $userId = $this->request->param('user_id', 0);
            $customers = CustomerModel::whereIn('id', $ids)->update(['user_id' => $userId]);
            $this->returnData['data'] = $customers;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';

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
}
