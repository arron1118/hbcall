<?php


namespace app\home\middleware;


use app\common\model\User;
use think\facade\Session;

class Check
{

    public function handle($request, \Closure $next)
    {
//        if ((!$request->cookie('hbcall_user_token') || User) && $request->action() !== 'login') {
//            return redirect(url('/index/login'));
//        }

        return $next($request);
    }

}
