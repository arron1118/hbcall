<?php


namespace app\company\controller;


use app\company\model\Company;
use think\facade\Session;

class User extends \app\common\controller\CompanyController
{


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
