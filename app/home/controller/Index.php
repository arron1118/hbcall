<?php

namespace app\home\controller;

use app\common\controller\HomeController;
use app\common\model\User;
use app\company\model\Company;
use arron\Random;
use Jenssegers\Agent\Agent;
use think\facade\Cookie;
use think\facade\Session;

class Index extends HomeController
{
    protected $token_expire_time = 3600 * 24 * 7;

    public function index()
    {
        return $this->view->fetch();
    }

    public function getCompanyBalance()
    {
        $balance = Company::where('id', '=', session::get('user.company_id'))->value('balance');
        return json($balance);
    }

    public function login()
    {
        if ($this->userInfo && $this->userInfo->token_expire_time > time()) {
            return redirect(url('/index'));
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
                $this->returnData['msg'] = lang('Account is incorrect');
                return json($this->returnData);
            }

            // 用户或者企业账号禁止状态都无法登录
            if (!$user->getData('status') || !$user->company->getData('status')) {
                $this->returnData['msg'] = lang('Account is locked');
                return json($this->returnData);
            }

            $password = getEncryptPassword($param['password'], $user->salt);
            if ($password !== $user->password) {
                $this->returnData['msg'] = lang('Password is incorrect');
                return json($this->returnData);
            }

            if (!captcha_check($param['captcha'])) {
                $this->returnData['msg'] = lang('Captcha is incorrect');
                return json($this->returnData);
            }

            $now = time();
            $token = createToken($password);
            $user->prevtime = $user->getData('logintime');
            $user->logintime = $now;
            $user->loginip = $this->request->ip();
            $user->token = $token;
            $user->token_expire_time = $now + $this->token_expire_time;
            $user->platform = $this->agent->platform() ?: '';
            $user->platform_version = $this->agent->version($this->agent->platform()) ?: '';
            $user->browser = $this->agent->browser() ?: '';
            $user->browser_version = $this->agent->version($this->agent->browser()) ?: '';
            $user->device = $this->agent->device() ?: '';

            $user->save();

            $user->session_id = Session::getId();

            Cookie::set('hbcall_user_token', $token, $this->token_expire_time);
            Session::set('user', $user->toArray());

            $balance = Company::where('id', '=', $user->company_id)->value('balance');
            Cookie::set('balance', $balance);
            Cookie::set('balance_tips', '');

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = lang('Logined');
            $this->returnData['url'] = (string)url('/index');
            return json($this->returnData);
        }
        return $this->view->fetch();
    }

    public function logout()
    {
        Session::delete('user');
        Cookie::delete('hbcall_user_token');
        Cookie::delete('balance_tips');
        Cookie::delete('balance');
        return redirect((string) url('/index/login'));
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
//        $user->saveAll($data);
    }


}
