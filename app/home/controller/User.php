<?php


namespace app\home\controller;

use app\common\model\User as UserModel;
use think\facade\Event;
use think\facade\Session;

class User extends \app\common\controller\HomeController
{

    public function profile()
    {
        $user = UserModel::find($this->userInfo['id']);

        if ($this->request->isPost()) {
            $username = trim($this->request->param('username'));
            $realname = trim($this->request->param('realname'));
            $phone = trim($this->request->param('phone'));
            if (empty($username)) {
                $this->returnData['msg'] = '请输入昵称';
                return json($this->returnData);
            }

            if (UserModel::where([['id', '<>', $this->userInfo['id']], ['username', '=', $username]])->count() > 0) {
                $this->returnData['msg'] = '昵称已存在';
                return json($this->returnData);
            }

            if (UserModel::where([['id', '<>', $this->userInfo['id']], ['phone', '=', $phone]])->count() > 0) {
                $this->returnData['msg'] = '手机号已存在';
                return json($this->returnData);
            }

            $user->username = $username;
            $user->realname = $realname;
            $user->phone = $phone;
            $user->save();
            Session::set('user.username', $username);
            Session::set('user.realname', $realname);
            Session::set('user.phone', $phone);

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';
            return json($this->returnData);
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
            $user = UserModel::find($this->userInfo['id']);
            if (empty($old_password)) {
                $this->returnData['msg'] = '请输入旧密码';
                return json($this->returnData);
            }
            if (empty($new_password)) {
                $this->returnData['msg'] = '请输入新密码';
                return json($this->returnData);
            }
            if (empty($confirm_password)) {
                $this->returnData['msg'] = '请输入确认密码';
                return json($this->returnData);
            }
            if (getEncryptPassword($old_password, $user->salt) !== $user->password) {
                $this->returnData['msg'] = '输入的旧密码有误';
                return json($this->returnData);
            }
            if ($new_password !== $confirm_password) {
                $this->returnData['msg'] = '输入的确认密码有误';
                return json($this->returnData);
            }
            $user->password = getEncryptPassword($confirm_password, $user->salt);
            $user->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';

            return json($this->returnData);
        }
        return $this->view->fetch();
    }
}
