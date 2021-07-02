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
        if ($this->isLogin()) {
            return redirect('/');
        }

        if ($this->request->isPost()) {
            /*$check = $this->request->checkToken('__token__');
            if(false === $check) {
                $token = $this->request->buildToken();
                return json(['data' => ['token' => $token], 'msg' => lang('Invalid token') . '，请重新提交', 'code' => 0]);
            }*/

            $param = $this->request->param();
            $user = User::getByUsername($param['username']);
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

    // 生成用户
    public function buildUsers()
    {
        $axb_number = [
            '13427445990',
            '13427445989',
            '13427445933',
            '13427445904',
            '13427445850',
            '13427445844',
            '13427445815',
            '13427445802',
            '13427445785',
            '13427445767',
            '13427445747',
            '13427445731',
            '13427445729',
            '13427445722',
            '13427445696',
            '13427445692',
            '13427445683',
            '13427445682',
            '13427445680',
            '13427445636'
        ];

        $user = new User();
        $data = [];
        foreach ($axb_number as $key => $val) {
            $salt = Random::alnum();
            $data[] = [
                'username' => '慧辰' . (int)($key + 1),
                'password' => getEncryptPassword('123456', $salt),
                'axb_number' => $val,
                'salt' => $salt
            ];
        }
        $user->saveAll($data);
    }



}
