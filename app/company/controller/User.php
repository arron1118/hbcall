<?php


namespace app\company\controller;

use app\common\model\NumberStore;
use app\common\model\UserXnumber;
use app\company\model\Company;
use app\common\model\User as UserModel;
use think\facade\Session;
use function Composer\Autoload\includeFile;

class User extends \app\common\controller\CompanyController
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
            $phone = $this->request->param('phone', '');
            $map = [
                ['company_id', '=', $this->userInfo->id]
            ];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($phone) {
                $map[] = ['phone', 'like', '%' . $phone . '%'];
            }
            $total = UserModel::where($map)->count();
            $userList = UserModel::with('userXnumber')
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

    public function checkLimitUser()
    {
        if ($this->userInfo->user_count === $this->userInfo->limit_user) {
            $this->returnData['msg'] = '已经达到限制开通用户数量';
            return json($this->returnData);
        }

        $this->returnData['code'] = 1;
        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['username'] = trim($params['username']);
            $params['salt'] = getRandChar(6);
            $params['password'] = getEncryptPassword(trim($params['password']), $params['salt']);

            if ($this->userInfo->user_count === $this->userInfo->limit_user) {
                $this->returnData['msg'] = '已经达到限制开通用户数量';
                return json($this->returnData);
            }

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
                if (!empty($params['number_store_id'])) {
                    (new UserXnumber())->save(['user_id' => $userModel->id, 'xnumber' => $this->getAxbNumber($params['number_store_id'])]);
                }
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
        $userInfo = UserModel::with('userXnumber')->find($userId);

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
                $userInfo->password = getEncryptPassword(trim($params['password']), $userInfo->salt);
            }

            $userInfo->username = $params['username'];
            $userInfo->phone = $params['phone'];

            if ($userInfo->save()) {
                $this->returnData['msg'] = '保存成功';
                $this->returnData['code'] = 1;

                // 设置座席
                if (!empty($params['number_store_id'])) {
                    $UserXnumber = UserXnumber::where('user_id', $userInfo->id)->find();
                    if ($UserXnumber) {
                        $UserXnumber->xnumber = $this->getAxbNumber($params['number_store_id']);
                        $UserXnumber->save();
                    } else {
                        (new UserXnumber())->save(['xnumber' => $this->getAxbNumber($params['number_store_id']), 'user_id' => $userInfo->id]);
                    }
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

    public function del()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->param('id', 0);
            if (!$userId) {
                $this->returnData['msg'] = '未提供正确的ID';
                return json($this->returnData);
            }

            $userInfo = UserModel::with('userXnumber')->find($userId);
            if ($userInfo->userXnumber()->delete()) {
                $userInfo->delete();
            }

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除成功';
            return json($this->returnData);
        }

        return json($this->returnData);
    }

    /**
     * 返回的小号列表
     * @return NumberStore[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getAxbNumbers()
    {
        return NumberStore::where(['company_id' => $this->userInfo['id'], 'status' => 1])->select()->toArray();
    }

    /**
     * 返回小号
     * @param $id
     * @return mixed
     */
    protected function getAxbNumber($id)
    {
        return NumberStore::where('id', $id)->value('number');
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $realname = trim($this->request->param('realname'));
            if (empty($username)) {
                return json(['msg' => '请输入昵称', 'code' => 0]);
            }
            $this->userInfo->realname = $realname;
            $this->userInfo->save();
            Session::set('company.realname', $realname);

            return json(['msg' => '操作成功', 'code' => 1]);
        }
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
            if ($user->save()) {
                return json(['msg' => '操作成功', 'code' => 1]);
            }

            return json($this->returnData);
        }
        return $this->view->fetch();
    }
}
