<?php


namespace app\company\middleware;


use app\company\model\Company;

class Check
{

    protected $noNeedLogin = ['login'];

    public function handle($request, \Closure $next)
    {
        if (!in_array($request->action(), $this->noNeedLogin)) {
            $token = $request->cookie('hbcall_company_token');
            if (!$this->checkToken($token)) {
                return redirect((string) url('/index/login'));
            }
        }

        return $next($request);
    }

    protected function checkToken($token)
    {
        if ($token) {
            $userInfo = Company::getByToken($token);
            if (!$userInfo || $userInfo->token_expire_time > time()) {
                return false;
            }

            return true;
        }

        return false;
    }
}
