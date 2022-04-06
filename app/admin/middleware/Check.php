<?php


namespace app\admin\middleware;


use app\admin\model\Admin;
use think\facade\Session;

class Check
{
    protected $noNeedLogin = ['login'];

    public function handle($request, \Closure $next)
    {
        if (!in_array($request->action(), $this->noNeedLogin)) {
            $token = $request->cookie('hbcall_admin_token');
            if (!$this->checkToken($token)) {
                return redirect((string) url('/index/login'));
            }
        }

        return $next($request);
    }

    protected function checkToken($token)
    {
        if ($token) {
            $userInfo = Admin::getByToken($token);
            if (!$userInfo || $userInfo->token_expire_time < time()) {
                return false;
            }

            return true;
        }

        return false;
    }
}
