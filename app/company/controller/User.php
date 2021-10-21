<?php


namespace app\company\controller;


use app\common\model\NumberStore;
use app\company\model\Company;
use app\common\model\User as UserModel;
use think\facade\Session;

class User extends \app\common\controller\CompanyController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function getUserList()
    {
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 10);
        $map = ['company_id' => Session::get('company.id')];
        $total = UserModel::where($map)->count();
        $userList = UserModel::with('axbNumber')->where($map)->limit(($page - 1) * $limit, $limit)->select();
        return json(['rows' => $userList, 'total' => $total, 'msg' => '操作成功', 'code' => 1]);
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

            if (UserModel::getByPhone($params['phone'])) {
                $this->returnData['msg'] = '手机号已经存在';
                return json($this->returnData);
            }

            $params['company_id'] = $this->userInfo['id'];

            $userModel = new UserModel();

            if ($userModel->save($params)) {
                $this->returnData['msg'] = '开通成功';
                $this->returnData['code'] = 1;

                // 设置座席
                NumberStore::where(['id' => $params['number_store_id']])
                    ->update(['user_id' => $userModel->id]);
            } else {
                $this->returnData['msg'] = '开通失败';
            }

            return json($this->returnData);
        }

        $this->view->assign('axbNumbers', $this->getAxbNumbers());

        return $this->view->fetch();
    }

    public function edit()
    {
        $userId = $this->request->param('id');
        $userInfo = UserModel::with('axbNumber')->find($userId);

        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['username'] = trim($params['username']);

            $existsName = UserModel::where('id <> ' . $params['id'] . ' and username = "' . $params['username'] . '"')->count();
            if ($existsName > 0) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (UserModel::where('id <> ' . $params['id'] . ' and phone = "' . $params['phone'] . '"')->count() > 0) {
                $this->returnData['msg'] = '手机号已经存在';
                return json($this->returnData);
            }

            if ($params['password'] !== $userInfo->password) {
                $params['salt'] = getRandChar(6);
                $userInfo->password = getEncryptPassword(trim($params['password']), $params['salt']);
            }

            $userInfo->username = $params['username'];
            $userInfo->phone = $params['phone'];

            if ($userInfo->save()) {
                $this->returnData['msg'] = '保存成功';
                $this->returnData['code'] = 1;

                // 设置座席
                if (!empty($params['number_store_id'])) {
                    NumberStore::where(['id' => $params['number_store_id']])
                        ->update(['user_id' => $userModel->id]);
                }
            } else {
                $this->returnData['msg'] = '保存失败';
            }

            return json($this->returnData);
        }

        $this->view->assign('userInfo', $userInfo);
        $this->view->assign('axbNumbers', $this->getAxbNumbers());
        return $this->view->fetch();
    }

    /**
     * 返回未分配的小号
     * @return NumberStore[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getAxbNumbers()
    {
        return NumberStore::where(['company_id' => $this->userInfo['id'], 'user_id' => 0, 'status' => 1])->select()->toArray();
    }

    public function profile()
    {
        $user = Company::find(Session::get('company.id'));

        if ($this->request->isPost()) {
            $username = trim($this->request->param('username'));
            $realname = trim($this->request->param('realname'));
            if (empty($username)) {
                return json(['msg' => '请输入昵称', 'code' => 0]);
            }
            $user->username = $username;
            $user->realname = $realname;
            $user->save();
            Session::set('company.username', $username);
            Session::set('company.realname', $realname);

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
            $user = Company::find(Session::get('company.id'));
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
