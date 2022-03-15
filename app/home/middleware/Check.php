<?php


namespace app\home\middleware;


use app\common\model\User;
use think\facade\Session;

class Check
{

    public function handle($request, \Closure $next)
    {
        if (!Session::has('user') && $request->action() !== 'login') {
            return redirect(url('/index/login'));
        }

        return $next($request);
    }

}
