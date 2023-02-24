<?php

namespace app\common\traits;

use app\common\model\Customer as CustomerModel;
use app\common\model\Company;
use think\db\exception\DbException;
use think\facade\View;

trait CustomerTrait
{

    public function index()
    {
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company);
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }

        return $this->view->fetch('common@customer/index');
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
            $companyId = (int) $this->request->param('company_id', $this->module === 'company' ? $this->userInfo->id : 0);
            $userId = (int) $this->request->param('user_id', $this->module === 'home' ? $this->userInfo->id : 0);

            $where = [];

            if ($userId > 0) {
                $where[] = ['user_id', '=', $userId];
            }

            if ($companyId > 0) {
                $where[] = ['company_id', '=', $companyId];
            }

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

            $res = CustomerModel::withCount(['record'])
                ->where($where)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit);

            if ($this->module !== 'home') {
                $res = $res->with(['company', 'user']);
            }

            $res = $res->select();

            $this->returnData['data'] = $res->hidden(['company', 'user']);
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['total'] = $total;

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function getLastImportCustomerList()
    {
        if ($this->request->isPost()) {
            $where = [
                'user_id' => $this->userInfo->id
            ];
            $lastData = CustomerModel::where($where)->order('id', 'desc')->findOrEmpty();
            if ($lastData->toArray()) {
                $lastDateStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->createtime))));
                $lastDateEnd = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->createtime))) + 3600 * 24 - 1);
                $res = CustomerModel::field('id, title, phone, called_count, last_calltime')
                    ->where($where)
                    ->whereBetweenTime('createtime', $lastDateStart, $lastDateEnd)
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

        return json($this->returnData);
    }

    public function CustomerRecordList()
    {
        $this->view->assign('customerId', $this->request->param('customerId'));
        return $this->view->fetch('common@customer/customer_record_list');
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '添加失败';
            $param = $this->request->param();

            if ($this->module === 'home') {
                $param['user_id'] = $this->userInfo->id;
                $param['company_id'] = $this->userInfo->company_id;
            } elseif ($this->module === 'company') {
                $param['company_id'] = $this->userInfo->id;
            }
            $param['createtime'] = time();
            $customer = new CustomerModel();
            if ($customer->save($param)) {
                $this->returnData['msg'] = '添加成功';
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }
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

    public function importExcel()
    {
        if ($this->request->isPost()) {
            $file = request()->file('file');
            // 1 允许导入重复客户 0 不允许导入重复客户
            $is_repeat_customer = $this->request->param('is_repeat_customer/d', 0);
            $data = $this->readExcel($file, $is_repeat_customer);
            try {
                $res = (new CustomerModel())->saveAll($data);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('The import was successful');
                $this->returnData['data'] = $res;
                $this->returnData['is_repeat_customer'] = $is_repeat_customer;

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

    protected function readExcel($file, $is_repeat_customer = 0)
    {
        $field = [
            'createtime' => time(),
        ];

        if ($this->module === 'home') {
            $field['company_id'] = $this->userInfo->company_id;
            $field['user_id'] = $this->userInfo->id;
        } else if ($this->module === 'company') {
            $field['company_id'] = $this->userInfo->id;
            $field['user_id'] = 0;
        }

        return readExcel($file, $field, $is_repeat_customer);
    }

    public function edit()
    {
        $customerId = (int) $this->request->param('id', 0);
        $customer = CustomerModel::find($customerId);

        if (!$customer) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $this->returnData['msg'] = '保存失败';
            if ($customer->save($this->request->param())) {
                $this->returnData['msg'] = '保存成功';
                $this->returnData['code'] = 1;
            }
            return json($this->returnData);
        }

        $this->returnData['data'] = $customer;
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '获取成功';
        $this->returnData['cateList'] = (new CustomerModel())->getCateList();
        return json($this->returnData);
    }

    public function del($id)
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '删除失败';
            if ($id > 0) {
                CustomerModel::destroy(function ($query) use ($id) {
                    $query->where('id', 'in', $id);
                });
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function updateCustomerCalledCount()
    {
        if ($this->request->isPost()) {
            $customerId = $this->request->param('customerId', 0);
            if ($customerId > 0) {
                $where = [
                    'user_id' => $this->userInfo->id,
                    'id' => $customerId,
                ];
                CustomerModel::where($where)->inc('called_count')->update(['last_calltime' => time()]);
            }
        }
    }
}
