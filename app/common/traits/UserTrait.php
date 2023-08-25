<?php

namespace app\common\traits;

use app\admin\model\Admin;
use app\admin\model\AdminSigninLogs;
use app\common\model\Company;
use app\common\model\CompanySigninLogs;
use app\common\model\User;
use app\common\model\UserSigninLogs;
use think\facade\Cookie;

trait UserTrait
{

    public function login()
    {
        if ($this->userInfo && $this->userInfo->token_expire_time > time() && $this->userInfo->status) {
            return redirect(url('/index'));
        }

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $model = User::class;
            $signinLogsModel = new UserSigninLogs();
            if ($this->module === 'admin') {
                $model = Admin::class;
                $signinLogsModel = new AdminSigninLogs();
            } elseif ($this->module === 'company') {
                $model = Company::class;
                $signinLogsModel = new CompanySigninLogs();
            }
            $user = $model::getByUsername($param['username']);
            if (!$user) {
                $this->returnData['msg'] = lang('Account is incorrect');
                return json($this->returnData);
            }

            $now = time();
            $user->token_expire_time = $now + $this->token_expire_time;

            if (!$user->status) {
                $this->returnData['msg'] = lang('Account is locked');
                return json($this->returnData);
            }

            // 试用用户试用期结束后禁止登录
            if ($this->module === 'home' && $user->is_test) {
                $user->token_expire_time = $user->getData('test_endtime');

                if ($user->getData('test_endtime') < $now) {
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

            ++$user->login_num;
            $user->token = createToken($password);
            $user->save();

            Cookie::set('hbcall_' . $this->module . '_token', $user->token, $this->token_expire_time);

            if (in_array($this->module, ['company', 'home'])) {
                Cookie::set('balance', $this->module === 'company' ? $user->balance : $user->company->balance);
            }

            // 登录日志
            if ($this->module === 'admin') {
                $signinLogsModel->admin_id = $user->id;
            } elseif ($this->module === 'company') {
                $signinLogsModel->company_id = $user->id;
            } else {
                $signinLogsModel->user_id = $user->id;
            }
            $signinLogsModel->ip = $this->request->ip();
            $signinLogsModel->device = $this->agent->device();
            $signinLogsModel->device_type = $this->agent->deviceType();
            $signinLogsModel->platform = $this->agent->platform();
            $signinLogsModel->platform_version = $this->agent->version($this->agent->platform());
            $signinLogsModel->browser = $this->agent->browser();
            $signinLogsModel->browser_version = $this->agent->version($this->agent->browser());
            $signinLogsModel->save();

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
            if ($old_password === $new_password) {
                $this->returnData['msg'] = lang('The new password is the same as the old password');
                return json($this->returnData);
            }
            if ($new_password !== $confirm_password) {
                $this->returnData['msg'] = lang('The confirmation password entered is incorrect');
                return json($this->returnData);
            }
            $oldPassword = $this->userInfo->password;
            $newPassword = getEncryptPassword($confirm_password, $this->userInfo->salt);
            $this->userInfo->password = $newPassword;
            if ($this->userInfo->save()) {
                $passwordLogs = [
                    'old_password' => $oldPassword,
                    'new_password' => $newPassword,
                ];
                if ($this->module === 'admin') {
                    $passwordLogs['admin_id'] = $this->userInfo->id;
                } elseif ($this->module === 'company') {
                    $passwordLogs['company_id'] = $this->userInfo->id;
                } else {
                    $passwordLogs['user_id'] = $this->userInfo->id;
                }
                $this->userInfo->passwordLogs()->save($passwordLogs);

                $this->returnData['msg'] = lang('Password modification successful, please log in again');
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }

        return $this->view->fetch('common@user/reset_password');
    }

    public function logout()
    {
        Cookie::delete('hbcall_' . $this->module . '_token');
        Cookie::delete('balance_tips');
        Cookie::delete('balance');

        $this->userInfo->token = '';
        $this->userInfo->token_expire_time = 0;
        $this->userInfo->save();

        return redirect((string) url('/user/login'));
    }

}
