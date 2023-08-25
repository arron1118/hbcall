<?php

namespace app\admin\controller;

use app\common\model\Company as CompanyModel;
use app\common\model\User as UserModel;
use app\common\model\NumberStore;
use app\common\traits\UserTrait;
use think\db\exception\DbException;

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
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $operate = $this->request->param('operate', '');
            $keyword = $this->request->param('keyword', '');
            $is_test = $this->request->param('is_test/d', -1);
            $status = $this->request->param('status/d', -1);
            $map = [];

            if ($is_test !== -1) {
                $map[] = ['is_test', '=', $is_test];
            }

            if ($status !== -1) {
                $map[] = ['status', '=', $status];
            }

            if ($operate) {
                $map[] = [$operate, 'like', '%' . $keyword . '%'];
            }

            $this->returnData['count'] = CompanyModel::where($map)->count();
            $this->returnData['data'] = CompanyModel::withCount('user')
                ->with(['companyXnumber' => ['numberStore']])
                ->where($map)->order('id', 'desc')
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->append(['call_type_text', 'is_test_text', 'status_text', 'call_status_text'])
                ->hidden(['salt', 'password', 'token', 'token_expire_time'])
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
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
            $company_id = $this->request->param('company_id/d');
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $operate = $this->request->param('operate', '');
            $keyword = $this->request->param('keyword', '');
            $map = [
                ['company_id', '=', $company_id]
            ];

            if ($operate) {
                $map[] = [$operate, 'like', '%' . $keyword . '%'];
            }
            $this->returnData['count'] = UserModel::where($map)->count();
            $this->returnData['data'] = UserModel::with(['userXnumber' => ['numberStore']])
                ->hidden(['password', 'salt'])
                ->withCount('callHistory')
                ->withSum('expense', 'cost')
                ->where($map)
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
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
            ->find($userId);
        if (!$userInfo) {
            $this->returnData['msg'] = lang('No data was found');
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
                $newPassword = getEncryptPassword(trim($data['password']), $userInfo->salt);
                $userInfo->passwordLogs()->save([
                    'company_id' => $userInfo->id,
                    'old_password' => $userInfo->password,
                    'new_password' => $newPassword,
                ]);

                $userInfo->password = $newPassword;
            }

            $userInfo->realname = $data['realname'];
            $userInfo->ration = $data['ration'];
            $userInfo->rate = $data['rate'];
            $userInfo->limit_user = $data['limit_user'];
            $userInfo->call_type = $data['call_type'];
            $userInfo->status = $data['status'] ?? 0;
            $userInfo->phone = $data['phone'];
            $userInfo->address = $data['address'];
            $userInfo->contract_attachment = $data['contract_attachment'];
            $userInfo->contract_start_datetime = strtotime($data['contract_start_datetime']);
            $userInfo->contract_end_datetime = strtotime($data['contract_end_datetime']);
            $userInfo->talent_on = $data['talent_on'] ?? 0;
            $userInfo->recycle_on = $data['recycle_on'] ?? 0;
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

                $this->returnData['data'] = $userInfo;

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
        }

        return json($this->returnData);
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $realname = trim($this->request->param('realname'));

            $this->userInfo->realname = $realname;
            if ($this->userInfo->save()) {
                $this->returnData['msg'] = lang('Operation successful');
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }

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

            $company = CompanyModel::with(['user'])->find($id);
            if ($company->together(['user'])->delete()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            } else {
                $this->returnData['msg'] = '删除失败';
            }
        }

        return json($this->returnData);
    }

    public function delUser()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->param('id', 0);
            $companyId = $this->request->param('company_id', 0);
            if (!$userId && !$companyId) {
                $this->returnData['msg'] = '未提供正确的ID';
                return json($this->returnData);
            }

            try {
                $userInfo = UserModel::where([
                    'company_id' => $companyId,
                ])->find($userId);

                $userInfo->together(['userXnumber'])->delete();

                // todo 回收用户的客户数据

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            } catch (DbException $exception) {
                $this->returnData['msg'] = $exception->getMessage();
            }
        }

        return json($this->returnData);
    }
}
