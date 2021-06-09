<?php

namespace app\home\controller;

use app\common\controller\HomeController;
use app\common\model\User;
use arron\Random;
use think\facade\Session;

class Index extends HomeController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function login()
    {
        if (Session::has('user')) {
            return redirect('/');
        }

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $user = User::getByUsername($param['username']);
            if (!$user) {
                return json(['data' => [], 'msg' => lang('Account is incorrect'), 'code' => 0]);
            }

            $password = getEncryptPassword($param['password'], $user->salt);
            if ($password !== $user->password) {
                return json(['data' => [], 'msg' => lang('Password is incorrect'), 'code' => 0]);
            }

            $user->prevtime = $user->logintime;
            $user->logintime = time();
            $user->loginip = $this->request->ip();

            $user->save();

            Session::set('user', $user->toArray());

            return json(['data' => [], 'msg' => lang('Logined'), 'code' => 1, 'url' => (string) url('/')]);
        }
        return $this->view->fetch();
    }

    public function logout()
    {
        Session::delete('user');
        return redirect('/');
    }

}
