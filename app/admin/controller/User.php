<?php

namespace app\admin\controller;

use app\common\model\Company as CompanyModel;
use app\common\model\User as UserModel;
use app\common\model\NumberStore;
use chillerlan\QRCode\Data\Number;
use think\facade\Session;
use app\common\traits\UserTrait;

class User extends \app\common\controller\AdminController
{
    use UserTrait;

    public function initialize()
    {
        parent::initialize();

        $this->view->assign([
            'callTypeList' => (new CompanyModel())->callTypeList(),
            'numberList' => NumberStore::select(),
        ]);
    }

    public function index()
    {
        $company = new CompanyModel();
        $this->view->assign([
            'isTestList' => $company->getTestList(),
            'statusList' => $company->getStatusList()
        ]);
        return $this->view->fetch();
    }

    public function getUserList()
    {
        if ($this->request->isAjax()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $corporation = $this->request->param('corporation', '');
            $is_test = (int) $this->request->param('is_test', -1);
            $status = (int) $this->request->param('status', -1);
            $map = [];

            if ($is_test !== -1) {
                $map[] = ['is_test', '=', $is_test];
            }

            if ($status !== -1) {
                $map[] = ['status', '=', $status];
            }

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($corporation) {
                $map[] = ['corporation', 'like', '%' . $corporation . '%'];
            }

            $total = CompanyModel::where($map)->count();
            $userList = CompanyModel::withCount('user')
                ->withSum(['payment' => function ($query) {
                    $query->where('status', 1);
                }], 'amount')
                ->with(['companyXnumber' => ['numberStore']])
                ->hidden(['salt'])
                ->where($map)->order('id', 'desc')
                ->order('id desc, logintime desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            return json(['rows' => $userList, 'total' => $total, 'msg' => '操作成功', 'code' => 1]);
        }

        return json($this->returnData);
    }

    public function subUserList()
    {
        $this->view->assign('company_id', $this->request->param('id'));
        return $this->view->fetch();
    }

    public function getSubUserList()
    {
        if ($this->request->isAjax()) {
            $company_id = (int) $this->request->param('company_id');
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $phone = $this->request->param('phone', '');
            $map = [
                ['company_id', '=', $company_id]
            ];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($phone) {
                $map[] = ['phone', 'like', '%' . $phone . '%'];
            }
            $total = UserModel::where($map)->count();
            $userList = UserModel::with(['userXnumber' => ['numberStore']])
                ->hidden(['password', 'salt'])
                ->withCount('callHistory')
                ->withSum('expense', 'cost')
                ->where($map)
                ->order('id desc, logintime desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            return json(['rows' => $userList, 'total' => $total, 'msg' => '操作成功', 'code' => 1]);
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['username'] = trim($params['username']);
            $params['salt'] = getRandChar(6);
            $params['password'] = getEncryptPassword(trim($params['password']), $params['salt']);

            if (CompanyModel::getByUsername($params['username'])) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (CompanyModel::getByCorporation($params['corporation'])) {
                $this->returnData['msg'] = '公司名称已经存在';
                return json($this->returnData);
            }

            if (intval($params['ration']) === 0) {
                $this->returnData['msg'] = '座席不能为 0';
                return json($this->returnData);
            }

            if (isset($params['is_test'])) {
                $params['is_test'] = (int) $params['is_test'];
                if ($params['is_test'] === 1) {
                    if ($params['test_endtime'] !== '') {
                        $params['test_endtime'] = strtotime($params['test_endtime']);
                        if ($params['test_endtime'] < time() - 1800) {
                            $this->returnData['msg'] = '结束时间不能小于现在时间';
                            return json($this->returnData);
                        }
                    } else {
                        $this->returnData['msg'] = '结束时间不能为空';
                        return json($this->returnData);
                    }
                }
            } else {
                unset($params['test_endtime']);
            }
//            if ($params['ration'] > 0 && NumberStore::where('status', '=', '0')->count() < $params['ration']) {
//                $this->returnData['msg'] = '剩余座席不足';
//                return json($this->returnData);
//            }

            if ($params['contract_start_datetime']) {
                $params['contract_start_datetime'] = strtotime($params['contract_start_datetime']);
            }

            if ($params['contract_end_datetime']) {
                $params['contract_end_datetime'] = strtotime($params['contract_end_datetime']);
            }

            $CompanyModel = new CompanyModel();

            if ($CompanyModel->save($params)) {
                $CompanyModel->companyXnumber()->save(['number_store_id' => $params['number_store_id']]);

                $this->returnData['msg'] = '开通成功';
                $this->returnData['code'] = 1;

                // 设置座席
//                if ($params['ration'] > 0) {
//                    NumberStore::where('status', '=', '0')
//                        ->limit($params['ration'])
//                        ->update(['company_id' => $CompanyModel->id, 'status' => 1]);
//                }
            } else {
                $this->returnData['msg'] = '开通失败';
            }

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function edit()
    {
        $userId = $this->request->param('id', 0);
        if ($userId <= 0) {
            $this->returnData['msg'] = '错误参数: ' . $userId;
            return json($this->returnData);
        }
        $userInfo = CompanyModel::withCount('user')
            ->with(['companyXnumber'])
            ->find($userId)
            ->withAttr('status', function ($value) {
                return $value;
            })
            ->withAttr('is_test', function ($value) {
                return $value;
            });
        if (!$userInfo) {
            $this->returnData['msg'] = '未找到数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $data = $this->request->param();

            if ((int)$data['limit_user'] !== 0 && $userInfo->user_count > $data['limit_user']) {
                $this->returnData['msg'] = '该公司已开通用户数大于限制用户数';
                return json($this->returnData);
            }

            if ((int)$data['ration'] === 0) {
                $this->returnData['msg'] = '座席不能为 0';
                return json($this->returnData);
            }

            if (isset($data['is_test'])) {
                $userInfo->is_test = (int) $data['is_test'];
                $this->returnData['data'] = $userInfo;
                if ((int) $data['is_test'] === 1) {
                    if ($data['test_endtime'] !== '') {
                        $userInfo->test_endtime = strtotime($data['test_endtime']);
                        if ($userInfo->test_endtime < time() - 1800) {
                            $this->returnData['msg'] = '结束时间不能小于现在时间';
                            return json($this->returnData);
                        }
                    } else {
                        $this->returnData['msg'] = '结束时间不能为空';
                        return json($this->returnData);
                    }
                }
            } else {
                $userInfo->is_test = 0;
                $userInfo->test_endtime = 0;
            }

            // 设置座席
//            if ($data['ration'] > 0) {
//                $hasNumbers = NumberStore::where('company_id', '=', $userId)->count();
//                $leftNumbers = NumberStore::where('status', '=', '0')->count();
//
//                if ($hasNumbers < $data['ration']) {
//                    if ($leftNumbers < ($data['ration'] - $hasNumbers)) {
//                        $this->returnData['msg'] = '剩余座席不足';
//                        return json($this->returnData);
//                    }
//
//                    NumberStore::where('company_id', '=', '0')
//                        ->limit($data['ration'] - $hasNumbers)
//                        ->update(['company_id' => $userId, 'status' => 1]);
//                }
//
//                if ($hasNumbers > $data['ration']) {
//                    NumberStore::where('company_id', '=', $userId)
//                        ->limit($hasNumbers - $data['ration'])
//                        ->order('id desc')
//                        ->update(['company_id' => 0, 'status' => 0]);
//                }
//            }

            if ($data['password'] && $data['password'] !== $userInfo->password) {
                $userInfo->password = getEncryptPassword(trim($data['password']), $userInfo->salt);
            }

            $userInfo->realname = $data['realname'];
            $userInfo->ration = $data['ration'];
            $userInfo->rate = $data['rate'];
            $userInfo->limit_user = $data['limit_user'];
            $userInfo->call_type = $data['call_type'];
            $userInfo->status = isset($data['status']) && $data['status'];
            $userInfo->phone = $data['phone'];
            $userInfo->address = $data['address'];
            $userInfo->contract_attachment = $data['contract_attachment'];
            $userInfo->contract_start_datetime = strtotime($data['contract_start_datetime']);
            $userInfo->contract_end_datetime = strtotime($data['contract_end_datetime']);
            if ($userInfo->save()) {
                // 更新企业小号关联表
                if ($userInfo->companyXnumber) {
                    $userInfo->companyXnumber->number_store_id = $data['number_store_id'];
                    $userInfo->companyXnumber->save();
                } else {
                    $userInfo->companyXnumber()->save(['number_store_id' => $data['number_store_id']]);
                }

                // 更新用户小号关联表
                if ($userInfo->user) {
                    foreach ($userInfo->user as $item) {
                        if ($item->userXnumber) {
                            $item->userXnumber->number_store_id = $data['number_store_id'];
                            $item->userXnumber->save();
                        } else {
                            $item->userXnumber()->save(['number_store_id' => $data['number_store_id']]);
                        }
                    }
                }

                $this->returnData['data'] = $userInfo->user;

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '更新完成';
            }

            return json($this->returnData);
        }

        $userInfo = $userInfo->append(['companyXnumber.number_store_id']);
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '获取成功';
        $this->returnData['data'] = [
            'userInfo' => $userInfo,
            'callTypeList' => (new CompanyModel())->getCalltypeList(),
        ];
        return json($this->returnData);
    }

    public function updateUser()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '更新失败';
            if ($this->request->has('id')) {
                $param = $this->request->param();
                $user = CompanyModel::find($param['id']);
                if ($user->save($param)) {
                    $this->returnData['msg'] = '更新成功';
                    $this->returnData['code'] = 1;
                }
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $realname = trim($this->request->param('realname'));

            $this->userInfo->realname = $realname;
            if ($this->userInfo->save()) {
                Session::set('admin.realname', $realname);

                $this->returnData['msg'] = lang('The operation succeeded');
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }

        $this->view->assign('userProfile', $this->userInfo);
        return $this->view->fetch();
    }

    public function del()
    {
        if ($this->request->isPost()) {
            $id = (int) $this->request->param('id', 0);
            if (!$id || $id === 1) {
                $this->returnData['msg'] = '请提供正确的参数';
                return json($this->returnData);
            }

            $company = CompanyModel::with(['companyXnumber'])->find($id);
            $users = UserModel::with(['userXnumber'])->where('company_id', $id)->select();
            foreach ($users as $key => $value) {
                if ($value->userXnumber()->delete()) {
                    $value->delete();
                }
            }
            if ($company->companyXnumber()->delete()) {
                $company->delete();
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            } else {
                $this->returnData['msg'] = '删除失败';
            }

            return json($this->returnData);
        }
    }
}
