<?php
/**
 * copyright@Administrator
 * 2023/8/25 0025 10:59
 * email:arron1118@icloud.com
 */

namespace app\common\subscribe;

use app\admin\model\Admin;
use app\common\model\Company;
use app\common\model\User as UserModel;
use Jenssegers\Agent\Agent;
use think\facade\Request;

class User
{

    public function onUserLogin($user)
    {
        if ($user instanceof \think\Model) {
            // 登录日志
            $agent = new Agent();
            $signinLogs = [
                'ip' => Request::ip(),
                'device' => $agent->device(),
                'device_type' => $agent->deviceType(),
                'platform' => $agent->platform(),
                'platform_version' => $agent->version($agent->platform()),
                'browser' => $agent->browser(),
                'browser_version' => $agent->version($agent->browser()),
                'username' => $user->username,
            ];

            if ($user instanceof Admin) {
                $signinLogs['admin_id'] = $user->id;
            } elseif ($user instanceof Company) {
                $signinLogs['company_id'] = $user->id;
            } elseif ($user instanceof UserModel) {
                $signinLogs['user_id'] = $user->id;
                $signinLogs['company_id'] = $user->company_id;
            }

            return $user->signinLogs()->save($signinLogs);
        }

        return false;
    }

    public function onChangePassword($param)
    {
        if ($param['user'] instanceof \think\Model) {
            // 密码修改日志
            $passwordLogs = [
                'old_password' => $param['oldPassword'],
                'new_password' => $param['newPassword'],
                'username' => $param['user']->username,
            ];

            if ($param['user'] instanceof Admin) {
                $passwordLogs['admin_id'] = $param['user']->id;
            } elseif ($param['user'] instanceof Company) {
                $passwordLogs['company_id'] = $param['user']->id;
            } elseif ($param['user'] instanceof UserModel) {
                $passwordLogs['user_id'] = $param['user']->id;
            }

            return $param['user']->passwordLogs()->save($passwordLogs);
        }

        return false;
    }

}
