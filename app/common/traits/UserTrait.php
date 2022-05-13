<?php

namespace app\common\traits;

use app\admin\model\Admin;
use app\common\model\Company;
use app\common\model\User;
use think\facade\Cookie;
use think\facade\Session;

trait UserTrait
{

    public function login()
    {
        if ($this->userInfo && $this->userInfo->token_expire_time > time() && $this->userInfo->getData('status')) {
            return redirect(url('/index'));
        }

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $model = User::class;
            $token_name = 'user';
            switch ($this->module) {
                case 'admin':
                    $model = Admin::class;
                    $token_name = 'admin';
                    break;
                case 'company':
                    $model = Company::class;
                    $token_name = 'company';
                    break;
            }
            $user = $model::getByUsername($param['username']);
            if (!$user) {
                $this->returnData['msg'] = lang('Account is incorrect');
                return json($this->returnData);
            }

            $now = time();
            $user->token_expire_time = $now + $this->token_expire_time;

            if (!$user->getData('status')) {
                $this->returnData['msg'] = lang('Account is locked');
                return json($this->returnData);
            }

            // 试用用户试用期结束后禁止登录
            if ($this->module === 'home' && $user->getData('is_test')) {
                $user->token_expire_time = $user->getData('test_endtime');

                if ($user->getData('test_endtime') < time()) {
                    $this->returnData['msg'] = '测试时间结束，' . lang('Account is locked');
                    return json($this->returnData);
                }
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

            $user->prevtime = $user->getData('logintime');
            $user->logintime = $now;
            $user->loginip = $this->request->ip();
            $user->token = createToken($password);
            $user->device = $this->agent->device();
            $user->platform = $this->agent->platform();
            $user->platform_version = $this->agent->version($this->agent->platform());
            $user->browser = $this->agent->browser();
            $user->browser_version = $this->agent->version($this->agent->browser());
            $user->save();

            Cookie::set('hbcall_' . $token_name . '_token', $user->token, $this->token_expire_time);
            Session::set($token_name, $user->toArray());

            if ($this->module === 'home') {
                $balance = Company::where('id', '=', $user->company_id)->value('balance');
                Cookie::set('balance', $balance);
                Cookie::set('balance_tips', '');
            }

            $this->returnData['msg'] = lang('Logined');
            $this->returnData['code'] = 1;
            $this->returnData['url'] = (string) url('/index');
            return json($this->returnData);
        }

        return $this->view->fetch('common@user/login');
    }

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $old_password = trim($this->request->param('old_password'));
            $new_password = trim($this->request->param('new_password'));
            $confirm_password = trim($this->request->param('confirm_password'));
            if (empty($old_password)) {
                $this->returnData['msg'] = lang('Please enter your old password');
                return json($this->returnData);
            }
            if (empty($new_password)) {
                $this->returnData['msg'] = lang('Please enter a new password');
                return json($this->returnData);
            }
            if (empty($confirm_password)) {
                $this->returnData['msg'] = lang('Please enter a confirmation password');
                return json($this->returnData);
            }
            if (getEncryptPassword($old_password, $this->userInfo->salt) !== $this->userInfo->password) {
                $this->returnData['msg'] = lang('The old password entered is incorrect');
                return json($this->returnData);
            }
            if ($new_password !== $confirm_password) {
                $this->returnData['msg'] = lang('The confirmation password entered is incorrect');
                return json($this->returnData);
            }
            $this->userInfo->password = getEncryptPassword($confirm_password, $this->userInfo->salt);
            if ($this->userInfo->save()) {
                $this->returnData['msg'] = lang('Password modification successful, please log in again');
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }

        return $this->view->fetch('common@user/reset_password');
    }

    public function logout()
    {
        switch ($this->module) {
            case 'admin':
                Session::delete('admin');
                Cookie::delete('hbcall_admin_token');
                break;
            case 'company':
                Session::delete('company');
                Cookie::delete('hbcall_company_token');
                break;
            case 'home':
                Session::delete('user');
                Cookie::delete('hbcall_user_token');
                Cookie::delete('balance_tips');
                Cookie::delete('balance');
                break;
        }

        $this->userInfo->token = '';
        $this->userInfo->token_expire_time = 0;
        $this->userInfo->save();

        return redirect((string) url('/user/login'));
    }

}
