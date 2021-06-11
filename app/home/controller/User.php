<?php


namespace app\home\controller;

use app\common\model\User as UserModel;
use think\facade\Event;
use think\facade\Session;

class User extends \app\common\controller\HomeController
{

    public function profile()
    {
        $user = UserModel::find(Session::get('user.id'));

        if ($this->request->isPost()) {
            $username = trim($this->request->param('username'));
            $realname = trim($this->request->param('realname'));
            if (empty($username)) {
                return json(['msg' => '请输入昵称', 'code' => 0]);
            }
            $user->username = $username;
            $user->realname = $realname;
            $user->save();
            Session::set('user.username', $username);
            Session::set('user.realname', $realname);

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
            $user = UserModel::find(Session::get('user.id'));
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
