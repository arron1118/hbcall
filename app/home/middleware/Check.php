<?php


namespace app\home\middleware;


use app\common\model\User;

class Check
{

    public function handle($request, \Closure $next)
    {
        if ((!$request->cookie('hbcall_user_token') || User::getByToken($request->cookie('hbcall_user_token'))) && $request->action() !== 'login') {
            return redirect(url('/index/login'));
        }

        return $next($request);
    }

}
