<?php

namespace app\company\controller;

use app\common\controller\CompanyController;
use app\company\model\Company;
use arron\Random;
use think\facade\Session;

class Index extends CompanyController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function dashboard()
    {
        return $this->view->fetch();
    }

    public function login()
    {
        if (Session::has('company')) {
            return redirect(url('/company'));
        }

        if ($this->request->isPost()) {
            /*$check = $this->request->checkToken('__token__');
            if(false === $check) {
                $token = $this->request->buildToken();
                return json(['data' => ['token' => $token], 'msg' => lang('Invalid token') . '，请重新提交', 'code' => 0]);
            }*/

            $param = $this->request->param();
            $user = Company::getByUsername($param['username']);
            if (!$user) {
                return json(['data' => [], 'msg' => lang('Account is incorrect'), 'code' => 0]);
            }

            if (!$user->status) {
                return json(['data' => [], 'msg' => lang('Account is locked'), 'code' => 0]);
            }

            $password = getEncryptPassword($param['password'], $user->salt);
            if ($password !== $user->password) {
                return json(['data' => [], 'msg' => lang('Password is incorrect'), 'code' => 0]);
            }

            if (!captcha_check($param['captcha'])) {
                return json(['data' => [], 'msg' => lang('Captcha is incorrect'), 'code' => 0]);
            }

            $user->prevtime = $user->getData('logintime');
            $user->logintime = time();
            $user->loginip = $this->request->ip();

            $user->save();

            Session::set('company', $user->toArray());

            return json(['data' => [], 'msg' => lang('Logined'), 'code' => 1, 'url' => (string)url('/')]);
        }
        return $this->view->fetch();
    }

    public function logout()
    {
        Session::delete('company');
        return redirect('/index/login');
    }

}
