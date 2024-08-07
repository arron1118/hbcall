<?php

namespace app\api\controller;

use app\common\controller\ApiController;
use app\common\model\Expense;
use app\common\model\UserSigninLogs;
use think\facade\Event;
use think\facade\Session;

class User extends ApiController
{

    protected $token_expire_time = 3600 * 24 * 7;

    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = 'success';

        $sessionId = Session::getId();

        $aesEncodeData = 'lFzJrxqaQle6gIcD/1Jh4LWMwblh7/Jzwv1ylTyw+LCUd3GSETsnHAqgJFzhjWTTth4W/HgcMtNGYhkGwXzVRnrTRdNNDuX4Etf94mS0Ex8aicPJSFUfhBRuCjdA1SIqbyy3V1CZsM1Sk/vARi1CD7hoHTYlnLus6tP1Wg83zLBZ2pzOgv0iF19OWY0f1BmK5nrkhMfPVBNR2t8GemYiPgTTMlb3rQ87GM7b3xgg4R4t+S2iVrG5yex1dc7qqWjMJZnonIVZKkIXFdo46NrpAe/Z94t+VZpKkxewLyxzCCAkZnyKgwfZcIE+4D2kC6mX+1zU5MANZRLzNktPFsr8OBBB7mvJ8x1PvABwLtapNHDaqoVrwJLdLC3pgtNXfozM/RZVLUA5hw46zYM+IKs8IiRH4wQOazU4A62CcThiBxvuk8ONE67SDUl9aIFmOI6coJjIemgjlumPYINQ/z6Fe33ShScGOnvld25yAT4SdaEjQ0oI9kdtl9146r7xYwmwT2GMFCQ1gHuoEec1CSrJa7g90a3Bj1Dc40ggzMpQNjhg1MRA68CM/l7cwJAdwPyQ9oKyhrs8gN2eqqYioTzzO4nxszBLhxjUvqwrhIhTX7+XPa2f682umZCf+aRs37p0zJhB9hpmNR07o8RbemVp7dFrGr4Mb85ztTRbQOBU9lf9miNZrgoLs1qHQEZyBa32x2xVPa4+U0LaZlHt9gP0f0X1jiXfbuQwTjxwYWL3p7U=';
        $this->returnData['data'] = [
            'openssl_config' => $this->aes->getConfig(),
            'openssl_encrypt' => $this->aes->aesEncode(json_encode(1, JSON_UNESCAPED_UNICODE)),
            'openssl_decrypt_json' => json_decode($this->aes->aesDecode($aesEncodeData), JSON_UNESCAPED_UNICODE),
        ];

        $this->returnApiData();
    }

    /**
     * 获取全球唯一标识
     * @return string
     */
    public function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * 用户登录
     * @return \think\response\Json
     */
    public function login()
    {
        if ($this->request->isPost()) {
            if (!isset($this->params['username']) || trim($this->params['username']) === '') {
                $this->returnData['msg'] = '参数错误：缺少 username';
                $this->returnApiData();
            }

            if (!isset($this->params['password']) || trim($this->params['password']) === '') {
                $this->returnData['msg'] = '参数错误：缺少 password';
                $this->returnApiData();
            }

            $model = ucfirst($this->userType) . 'Model';
            $user = $this->$model::where('username', $this->params['username'])
                ->find();

            if (!$user) {
                $this->returnData['msg'] = lang('Account is incorrect');
                $this->returnApiData();
            }

            if (!$user->status) {
                $this->returnData['msg'] = lang('Account is locked');
                $this->returnApiData();
            }

            $now = time();
            $user->token_expire_time = $now + $this->token_expire_time;

            // 试用用户试用期结束后禁止登录
            if ($this->userType === 'user' && $user->is_test) {
                $user->token_expire_time = $user->token_expire_time;

                if ($user->getData('test_endtime') < time()) {
                    $this->returnData['msg'] = '测试时间结束，' . lang('Account is locked');
                    $this->returnApiData();
                }
            }

            $password = getEncryptPassword($this->params['password'], $user->salt);
            if ($password !== $user->password) {
                $this->returnData['msg'] = lang('Password is incorrect');
                $this->returnApiData();
            }

            ++$user->login_num;
            $user->token = createToken($password);
            $user->save();

            $signinLogsModel = new UserSigninLogs();
            $signinLogsModel->user_id = $user->id;
            $signinLogsModel->ip = $this->request->ip();
            $signinLogsModel->device = $this->agent->device();
            $signinLogsModel->device_type = $this->agent->deviceType();
            $signinLogsModel->platform = $this->agent->platform();
            $signinLogsModel->platform_version = $this->agent->version($this->agent->platform());
            $signinLogsModel->browser = $this->agent->browser();
            $signinLogsModel->browser_version = $this->agent->version($this->agent->browser());
            $signinLogsModel->save();

            Event::trigger('UserLogin', $user);

            $where = [$this->userType . '_id' => $user->id];
            $user->yesterday_duration = Expense::where($where)
                ->whereRaw("date_format(date_sub(now(), interval 1 day), '%Y-%m-%d') = from_unixtime(create_time, '%Y-%m-%d')")
                ->sum('duration');
            $user->today_duration = Expense::where($where)
                ->whereRaw("date_format(now(), '%Y-%m-%d') = from_unixtime(create_time, '%Y-%m-%d')")
                ->sum('duration');

            Session::set('api_' . $this->userType, $user->toArray());

            $user->call_type_id = $user->company->call_type_id;

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = lang('logined');
            $this->returnData['data'] = $user->hidden(['password', 'salt', 'company'])->toArray();
        }

        $this->returnApiData();
    }

    public function getUserProfile()
    {
        return json($this->getUserInfo());
    }

    public function checkLogin()
    {
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = 'Success';
        $this->returnData['data'] = [
            'login' => $this->isLogin(),
        ];

        $this->returnApiData();
    }

    public function logout()
    {
        Session::delete('api_' . $this->userType);
        $this->userInfo->token = '';
        $this->userInfo->token_expire_time = 0;
        $this->userInfo->save();
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '退出成功';
        $this->returnApiData();
    }

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            if (empty($this->params['old_password'])) {
                $this->returnData['msg'] = '请输入旧密码';
                $this->returnApiData();
            }

            if (empty($this->params['new_password'])) {
                $this->returnData['msg'] = '请输入新密码';
                $this->returnApiData();
            }

            if (empty($this->params['confirm_password'])) {
                $this->returnData['msg'] = '请输入确认密码';
                $this->returnApiData();
            }

            if (getEncryptPassword($this->params['old_password'], $this->userInfo->salt) !== $this->userInfo->password) {
                $this->returnData['msg'] = '输入的旧密码有误';
                $this->returnApiData();
            }

            if ($this->params['new_password'] !== $this->params['confirm_password']) {
                $this->returnData['msg'] = '输入的确认密码有误';
                $this->returnApiData();
            }
            $newPassword = getEncryptPassword($this->params['confirm_password'], $this->userInfo->salt);
            $this->userInfo->token = '';
            $this->userInfo->token_expire_time = 0;

            // 密码修改日志
            Event::trigger('ChangePassword', [
                'user' => $this->userInfo,
                'oldPassword' => $this->userInfo->password,
                'newPassword' => $newPassword,
            ]);

            $this->userInfo->password = $newPassword;

            if ($this->userInfo->save()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = lang('Password modification successful, please log in again');
                $this->returnApiData();
            }

            $this->returnData['msg'] = lang('Password modification successful, please log in again');
            $this->returnApiData();
        }

        $this->returnApiData();
    }
}
