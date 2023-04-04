<?php

namespace app\common\traits;

use app\common\model\Customer as CustomerModel;
use app\common\model\Company;
use app\common\model\CustomerPhoneRecord;
use think\db\exception\DbException;

trait CustomerTrait
{
    protected $type = 1;

    /**
     * 客户管理
     * @return mixed
     */
    public function index()
    {
        $this->type = 1;
        return $this->init();
    }

    /**
     * 人才管理
     * @return mixed
     */
    public function talent()
    {
        $this->type = 2;
        return $this->init();
    }

    public function init()
    {
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company);
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }

        $CustmoerModel = new CustomerModel();
        $this->view->assign([
            'type' => $this->type,
            'cateList' => $CustmoerModel->getCateList($this->type),
            'typeText' => $CustmoerModel->getTypeList()[$this->type],
        ]);

        return $this->view->fetch('common@customer/index');
    }

    public function getCustomerList()
    {
        if ($this->request->isPost()) {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $operate = trim($this->request->param('operate', ''));
            $keyword = trim($this->request->param('keyword', ''));
            $status = (int) $this->request->param('status', -1);
            $cate = (int) $this->request->param('cate', -1);
            $companyId = (int) $this->request->param('company_id', $this->module === 'company' ? $this->userInfo->id : 0);
            $userId = (int) $this->request->param('user_id', $this->module === 'home' ? $this->userInfo->id : 0);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $type = $this->request->param('type', 0);

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

            if ($type > 0) {
                $where[] = ['type', '=', $type];
            }

            if ($keyword) {
                $where[] = [$operate, 'like', '%' . $keyword . '%'];
            }

            if ($status === 0) {
                $where[] = ['user_id', '=', 0];
            } elseif ($status === 1) {
                $where[] = ['user_id', '>', 0];
            }

            if ($startDate && $endDate) {
                $where[] = ['create_time', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            $total = CustomerModel::where($where)->count();

            $res = CustomerModel::with(['company', 'user'])->withCount(['record'])
                ->where($where)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit);

//            if ($this->module !== 'home') {
//                $res = $res->with(['company', 'user']);
//            }

            $res = $res->append(['cate_text'])->select();

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
                $lastDateStart = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->create_time))));
                $lastDateEnd = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($lastData->create_time))) + 3600 * 24 - 1);
                $res = CustomerModel::field('id, title, phone, called_count, last_calltime')
                    ->where($where)
                    ->whereBetweenTime('create_time', $lastDateStart, $lastDateEnd)
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
            $param['create_time'] = time();
            $customer = new CustomerModel();
            if ($customer->save($param)) {
                $this->returnData['msg'] = '添加成功';
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }
    }

    /**
     * 移动分类
     * @return \think\response\Json
     */
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
            try {
                $data = $this->readExcel();
                $res = (new CustomerModel())->saveAll($data);

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

    protected function readExcel()
    {
        $file = request()->file('file');
        // 1 允许导入重复客户 0 不允许导入重复客户
        $is_repeat_customer = $this->request->param('is_repeat_customer/d', 0);
        $field = [
            'create_time' => time(),
            'type' => $this->request->param('type/d'),
        ];

        if ($this->module === 'home') {
            $field['company_id'] = $this->userInfo->company_id;
            $field['user_id'] = $this->userInfo->id;
        } elseif ($this->module === 'company') {
            $field['company_id'] = $this->userInfo->id;
            $field['user_id'] = 0;
        }

        return readExcel($file, $field, $is_repeat_customer);
    }

    public function edit()
    {
        $params = $this->request->param();
        $customerModel = new CustomerModel();
        if ($this->module === 'home') {
            $customerModel = $customerModel->where([
                'user_id' => $this->userInfo->id,
            ]);
        } elseif ($this->module === 'company') {
            $customerModel = $customerModel->where([
                'company_id' => $this->userInfo->id,
            ]);
        }

        $customer = $customerModel->find($params['id']);

        if (!$customer) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $this->returnData['msg'] = '保存失败';
            unset($params['phone']);
            if ($customer->save($params)) {
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
                $module = $this->module;
                $user = $this->userInfo;

                CustomerModel::destroy(function ($query) use ($id, $module, $user) {
                    if ($module === 'home') {
                        $query->where([
                            'user_id' => $user->id,
                        ]);
                    } elseif ($module === 'company') {
                        $query->where([
                            'company_id' => $user->id,
                        ]);
                    }
                    $query->where('id', 'in', $id);
                });
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    /**
     * 获取客户电话
     * @param $id
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCustomerPhone($id)
    {
        if ($this->request->isAjax()) {
            if (!$id) {
                $this->returnData['msg'] = '错误参数';
                return json($this->returnData);
            }
            $customerModel = new CustomerModel();
            $customerPhoneRecord = new CustomerPhoneRecord();
            if ($this->module === 'home') {
                $viewNum = CustomerPhoneRecord::where([
                    'customer_id' => $id,
                    'user_id' => $this->userInfo->id,
                ])->count();
                if ($this->userInfo->customer_view_num !== 0 && ($viewNum >= $this->userInfo->customer_view_num)) {
                    $this->returnData['msg'] = '查看次数已达到上限';
                    return json($this->returnData);
                }

                $customerPhoneRecord->company_id = $this->userInfo->company_id;
                $customerPhoneRecord->company_name = $this->userInfo->company->username;
                $customerPhoneRecord->user_id = $this->userInfo->id;
                $customerPhoneRecord->user_name = $this->userInfo->username;

                $customerModel = $customerModel->where([
                    'user_id' => $this->userInfo->id,
                ]);
            } elseif ($this->module === 'company') {
                $customerPhoneRecord->company_id = $this->userInfo->id;
                $customerPhoneRecord->company_name = $this->userInfo->username;

                $customerModel = $customerModel->where([
                    'company_id' => $this->userInfo->id,
                ]);
            } elseif ($this->module === 'admin') {
                $customerPhoneRecord->admin_id = $this->userInfo->id;
                $customerPhoneRecord->admin_name = $this->userInfo->username;
            }

            $customer = $customerModel->find($id);
            if ($customer) {
                $this->returnData['data'] = $customer->getData('phone');
                $this->returnData['msg'] = '获取成功';
                $this->returnData['code'] = 1;

                // 保存查看记录
                $customerPhoneRecord->customer_id = $customer->id;
                $customerPhoneRecord->customer_phone = $customer->getData('phone');
                $customerPhoneRecord->customer_title = $customer->title;
                $customerPhoneRecord->save();

                // 更新查看数量
                $customer->check_phone_num += 1;
                $customer->save();
            }
            return json($this->returnData);
        }

        return json($this->returnData);
    }

    /**
     * 分配人才
     * @return \think\response\Json
     */
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
}
