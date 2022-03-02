<?php


namespace app\admin\controller;


use app\company\model\Company as UserModel;
use app\admin\model\Admin;
use app\common\model\NumberStore;
use think\facade\Session;

class User extends \app\common\controller\AdminController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function getUserList()
    {
        if ($this->request->isAjax()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $corporation = $this->request->param('corporation', '');
            $map = [];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($corporation) {
                $map[] = ['corporation', 'like', '%' . $corporation . '%'];
            }

            $total = UserModel::where($map)->count();
            $userList = UserModel::withCount('user')
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

            if (UserModel::getByUsername($params['username'])) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (UserModel::getByCorporation($params['corporation'])) {
                $this->returnData['msg'] = '公司名称已经存在';
                return json($this->returnData);
            }

            if (intval($params['ration']) === 0) {
                $this->returnData['msg'] = '座席不能为 0';
                return json($this->returnData);
            }

            if ($params['ration'] > 0 && NumberStore::where('status', '=', '0')->count() < $params['ration']) {
                $this->returnData['msg'] = '剩余座席不足';
                return json($this->returnData);
            }

            $userModel = new UserModel();

            if ($userModel->save($params)) {
                $this->returnData['msg'] = '开通成功';
                $this->returnData['code'] = 1;

                // 设置座席
                if ($params['ration'] > 0) {
                    NumberStore::where('status', '=', '0')
                        ->limit($params['ration'])
                        ->update(['company_id' => $userModel->id, 'status' => 1]);
                }
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
        $userInfo = UserModel::withCount('user')->find($userId);
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

            // 设置座席
            if ($data['ration'] > 0) {
                $hasNumbers = NumberStore::where('company_id', '=', $userId)->count();
                $leftNumbers = NumberStore::where('status', '=', '0')->count();

                if ($hasNumbers < $data['ration']) {
                    if ($leftNumbers < ($data['ration'] - $hasNumbers)) {
                        $this->returnData['msg'] = '剩余座席不足';
                        return json($this->returnData);
                    }

                    NumberStore::where('company_id', '=', '0')
                        ->limit($data['ration'] - $hasNumbers)
                        ->update(['company_id' => $userId, 'status' => 1]);
                }

                if ($hasNumbers > $data['ration']) {
                    NumberStore::where('company_id', '=', $userId)
                        ->limit($hasNumbers - $data['ration'])
                        ->order('id desc')
                        ->update(['company_id' => 0, 'status' => 0]);
                }
            }

            if ($data['password'] !== $userInfo->password) {
                $userInfo->password = getEncryptPassword(trim($data['password']), $userInfo->salt);
            }

            $userInfo->ration = $data['ration'];
            $userInfo->rate = $data['rate'];
            $userInfo->limit_user = $data['limit_user'];
            if ($userInfo->save()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '更新完成';
            }

            return json($this->returnData);
        }
        $this->view->assign('userInfo', $userInfo);
        return $this->view->fetch();
    }


    public function profile()
    {
        $user = admin::find(Session::get('admin.id'));

        if ($this->request->isPost()) {
            $username = trim($this->request->param('username'));
            $realname = trim($this->request->param('realname'));
            if (empty($username)) {
                return json(['msg' => '请输入昵称', 'code' => 0]);
            }
            $user->username = $username;
            $user->realname = $realname;
            $user->save();
            Session::set('admin.username', $username);
            Session::set('admin.realname', $realname);

            return json(['msg' => '操作成功', 'code' => 1]);
        }
        $this->view->assign('userProfile', $user);
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
                return json(['msg' => '请输入旧密码', 'code' => 0]);
            }
            if (empty($new_password)) {
                return json(['msg' => '请输入新密码', 'code' => 0]);
            }
            if (empty($confirm_password)) {
                return json(['msg' => '请输入确认密码', 'code' => 0]);
            }
            if (getEncryptPassword($old_password, $user->salt) !== $user->password) {
                return json(['msg' => '输入的旧密码有误', 'code' => 0]);
            }
            if ($new_password !== $confirm_password) {
                return json(['msg' => '输入的确认密码有误', 'code' => 0]);
            }
            $user->password = getEncryptPassword($confirm_password, $user->salt);
            $user->save();

            return json(['msg' => '操作成功', 'code' => 1]);
        }
        return $this->view->fetch();
    }


}
