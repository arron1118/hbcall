<?php

namespace app\api\controller;

use app\common\controller\ApiController;
use think\facade\Session;

class User extends ApiController
{
    public function index()
    {
        dump($this->getUserInfo());
        dump($this->isLogin());
    }

    public function login()
    {
        /*if ($this->isLogin()) {
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = lang('You are already logged in');
            $this->returnData['data'] = Session::get('api_user');
            return json($this->returnData);
        }*/

        if ($this->request->isPost()) {
            /*$check = $this->request->checkToken('__token__');
            if(false === $check) {
                $token = $this->request->buildToken();
                return json(['data' => ['token' => $token], 'msg' => lang('Invalid token') . '，请重新提交', 'code' => 0]);
            }*/

            $param = $this->request->param();

            if (!isset($param['username']) || trim($param['username']) === '') {
                $this->returnData['msg'] = '参数错误：缺少 username';
                return json($this->returnData);
            }

            if (!isset($param['password']) || trim($param['password']) === '') {
                $this->returnData['msg'] = '参数错误：缺少 password';
                return json($this->returnData);
            }

            $user = \app\common\model\User::getByUsername($param['username']);
            if (!$user) {
                $this->returnData['msg'] = lang('Account is incorrect');
                return json($this->returnData);
            }

            if (!$user->status) {
                $this->returnData['msg'] = lang('Account is locked');
                return json($this->returnData);
            }

            $password = getEncryptPassword($param['password'], $user->salt);
            if ($password !== $user->password) {
                $this->returnData['msg'] = lang('Password is incorrect');
                return json($this->returnData);
            }

            /*if (!captcha_check($param['captcha'])) {
                $this->returnData['msg'] = lang('Captcha is incorrect');
                return json($this->returnData);
            }*/

            $user->prevtime = $user->getData('logintime');
            $user->logintime = time();
            $user->loginip = $this->request->ip();
            $user->save();

            Session::set('api_user', $user->toArray());

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = lang('logined');
            $this->returnData['data'] = $user->hidden(['password', 'salt', 'company_id', 'axb_number'])->toArray();

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function logout()
    {
        Session::delete('api_user');
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '退出成功';
        return json($this->returnData);
    }
}
