<?php


namespace app\company\middleware;


use app\company\model\Company;

class Check
{
    public function handle($request, \Closure $next)
    {
        if ((!$request->cookie('hbcall_company_token') || !Company::getByToken($request->cookie('hbcall_company_token'))) && $request->action() !== 'login') {
            return redirect((string) url('/index/login'));
        }

        return $next($request);
    }

}
