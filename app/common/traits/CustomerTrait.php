<?php

namespace app\common\traits;

use app\common\model\Customer as CustomerModel;
use app\common\model\Company;
use app\common\model\CustomerPhoneRecord;
use app\common\model\User;
use think\db\exception\DbException;
use think\facade\Event;

trait CustomerTrait
{
    protected $type = 1;

    protected $searchItem = [
        'title' => '名称',
        'phone' => '联系电话',
        'comment' => '备注',
    ];

    protected function initialize()
    {
        parent::initialize();

        $this->type = $this->request->param('type/d', 1);
        $this->searchItem = (new CustomerModel)->getSearchItem($this->type);

        // 监控用户客户数据限制
        if ($this->module === 'home') {
            Event::trigger('Customer', $this->userInfo);
        }
    }

    /**
     * 客户管理
     * @return mixed
     */
    public function index()
    {
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
            $this->view->assign('company', $company->toArray());
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }

        $customerModel = new CustomerModel();
        $this->view->assign([
            'type' => $this->type,
            'cateList' => $customerModel->getCateList($this->type),
            'typeText' => $customerModel->getTypeList()[$this->type],
            'searchItem' => $customerModel->getSearchItem($this->type),
        ]);

        return $this->view->fetch('common@customer/index');
    }

    public function trash_list()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $companyId = $this->request->param('company_id/d', $this->module === 'company' ? $this->userInfo->id : 0);
            $userId = $this->request->param('user_id/d', $this->module === 'home' ? $this->userInfo->id : 0);
            $where = [];

            if ($userId > 0) {
                $where[] = ['user_id', '=', $userId];
            }

            if ($companyId > 0) {
                $where[] = ['company_id', '=', $companyId];
            }

            if ($this->type > 0) {
                $where[] = ['type', '=', $this->type];
            }
            $this->returnData['msg'] = lang('Operation successful');
            $this->returnData['count'] = CustomerModel::onlyTrashed()->where($where)->count();
            $this->returnData['data'] = CustomerModel::onlyTrashed()
                ->where($where)
                ->with(['company', 'user'])
                ->withCount(['record'])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->append(['cate_text'])
                ->hidden(['company', 'user'])
                ->select();

            return json($this->returnData);
        }

        $this->view->assign([
            'type' => $this->type,
            'typeText' => (new CustomerModel)->getTypeList()[$this->type],
        ]);
        return $this->view->fetch('common@customer/customer_trash_list');
    }

    public function getCustomerList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $operate = trim($this->request->param('operate/s', ''));
            $keyword = trim($this->request->param('keyword/s', ''));
            $status = $this->request->param('status/d', -1);
            $cate = $this->request->param('cate/d', -1);
            $companyId = $this->request->param('company_id/d', $this->module === 'company' ? $this->userInfo->id : 0);
            $userId = $this->request->param('user_id/d', $this->module === 'home' ? $this->userInfo->id : 0);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
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

            if ($this->type > 0) {
                $where[] = ['type', '=', $this->type];
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

            $userInfo = $this->userInfo;
            $module = $this->module;
            $this->returnData['count'] = CustomerModel::where($where)->count();
            $this->returnData['data'] = CustomerModel::with(['company', 'user'])
                ->withCount(['record' => function ($query) use ($userInfo, $module) {
                    if ($module === 'home') {
                        $query->where('user_id', $userInfo->id);
                    }
                }])
                ->where($where)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->append(['cate_text'])
                ->hidden(['company', 'user'])
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
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
            if (!$lastData->isEmpty()) {
                $lastDate = strtotime(date('Y-m-d', strtotime($lastData->create_time)));
                $lastDateStart = date('Y-m-d H:i:s', $lastDate);
                $lastDateEnd = date('Y-m-d H:i:s', $lastDate + 3600 * 24 - 1);
                $this->returnData['data'] = CustomerModel::field('id, title, phone, called_count, last_calltime')
                    ->where($where)
                    ->whereBetweenTime('create_time', $lastDateStart, $lastDateEnd)
                    ->order('called_count')
                    ->order('id', 'desc')
                    ->select();
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('Operation successful');
            }
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
                if ($this->checkCustomerNumForUser($this->userInfo)) {
                    $this->returnData['msg'] = '已超出数量限制。如有问题请联系管理员';
                    return json($this->returnData);
                }
                $param['user_id'] = $this->userInfo->id;
                $param['company_id'] = $this->userInfo->company_id;
                $param['distribution_time'] = time();
            } elseif ($this->module === 'company') {
                $param['company_id'] = $this->userInfo->id;
            }

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
            if ($this->module === 'home' && $this->checkCustomerNumForUser($this->userInfo)) {
                $this->returnData['msg'] = '已超出数量限制。如有问题请联系管理员';
                return json($this->returnData);
            }

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

        $limitNum = 0;
        if ($this->module === 'home') {
            $field['company_id'] = $this->userInfo->company_id;
            $field['user_id'] = $this->userInfo->id;
            $filed['distribution_time'] = time();
            $limit = $this->type === 1 ? $this->userInfo->customer_num : $this->userInfo->talent_num;
            $limit && $limitNum = $limit + 1 - CustomerModel::where([
                    'user_id' => $this->userInfo->id,
                    'type' => $this->type,
                ])->whereIn('cate', [0, 3])->count();
        } elseif ($this->module === 'company') {
            $field['company_id'] = $this->userInfo->id;
            $field['user_id'] = 0;
        }

        return readExcel($file, $field, $is_repeat_customer, $limitNum);
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
            if ($id) {
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
        }

        return json($this->returnData);
    }

    protected function checkCustomerNumForUser(User $user)
    {
        $number = $this->type === 1 ? $user->customer_num : $user->talent_num;

        $result = [];
        if ($number) {
            CustomerModel::where([
                ['cate', 'in', [0, 3]],
                ['type', '=', $this->type],
                ['user_id', '=', $user->id]
            ])->order('id', 'desc')
                ->limit($number, 1)
                ->column('id');
        }

//        var_dump($result);
        return $result;
    }
}
