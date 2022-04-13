<?php

namespace app\admin\controller;

use app\company\model\Company as CompanyModel;
use app\common\model\User as UserModel;
use app\admin\model\Admin;
use app\common\model\NumberStore;
use think\facade\Session;

class User extends \app\common\controller\AdminController
{
    public function index()
    {
        $this->view->assign('isTestList', (new CompanyModel())->getTestList());
        $this->view->assign('statusList', (new CompanyModel())->getStatusList());
        return $this->view->fetch();
    }

    public function getUserList()
    {
        if ($this->request->isAjax()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $corporation = $this->request->param('corporation', '');
            $is_test = $this->request->param('is_test', -1);
            $map = [];

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
            }
//            if ($params['ration'] > 0 && NumberStore::where('status', '=', '0')->count() < $params['ration']) {
//                $this->returnData['msg'] = '剩余座席不足';
//                return json($this->returnData);
//            }

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
        $numberList = NumberStore::select();
        $this->view->assign('numberList', $numberList);

        return $this->view->fetch();
    }

    public function edit()
    {
        $userId = $this->request->param('id', 0);
        if ($userId <= 0) {
            $this->returnData['msg'] = '错误参数: ' . $userId;
            return json($this->returnData);
        }
        $userInfo = CompanyModel::withCount('user')->find($userId);
        if (!$userInfo) {
            $this->returnData['msg'] = '未找到数据';
            return json($this->returnData);
        }

        if ($this->request->isAjax()) {
            $data = $this->request->param();

            if (intval($data['limit_user']) !== 0 && $userInfo->user_count > $data['limit_user']) {
                $this->returnData['msg'] = '该公司已开通用户数大于限制用户数';
                return json($this->returnData);
            }

            if (intval($data['ration']) === 0) {
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

            $userInfo->ration = $data['ration'];
            $userInfo->rate = $data['rate'];
            $userInfo->limit_user = $data['limit_user'];
            $userInfo->status = isset($data['status']) && $data['status'];
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

        $numberList = NumberStore::select();
        $this->view->assign('numberList', $numberList);

        $this->view->assign('userInfo', $userInfo);
        return $this->view->fetch();
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

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $old_password = trim($this->request->param('old_password'));
            $new_password = trim($this->request->param('new_password'));
            $confirm_password = trim($this->request->param('confirm_password'));
            $user = Admin::find(Session::get('admin.id'));
            if (empty($old_password)) {
                $this->returnData['msg'] = lang('Please enter your old password');
                return json($this->returnData);
            }
            if (empty($new_password)) {
                $this->returnData['msg'] = lang('Please enter a new password');
                return json($this->returnData);
            }
            if (empty($confirm_password)) {
                $this->returnData['msg'] = lang('Please enter a confirmation password');
                return json($this->returnData);
            }
            if (getEncryptPassword($old_password, $user->salt) !== $user->password) {
                $this->returnData['msg'] = lang('The old password entered is incorrect');
                return json($this->returnData);
            }
            if ($new_password !== $confirm_password) {
                $this->returnData['msg'] = lang('The confirmation password entered is incorrect');
                return json($this->returnData);
            }
            $user->password = getEncryptPassword($confirm_password, $user->salt);
            if ($user->save()) {
                $this->returnData['msg'] = lang('Password modification successful, please log in again');
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
