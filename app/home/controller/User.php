<?php


namespace app\home\controller;

use app\common\model\User as UserModel;
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
        return $this->view->fetch();
    }
}
