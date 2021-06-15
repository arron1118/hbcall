<?php


namespace app\home\middleware;


use think\facade\Session;

class Check
{

    public function handle($request, \Closure $next)
    {
        if (!Session::has('user') && $request->action() !== 'login') {
            return redirect(url('home/index/login'));
        }

        return $next($request);
    }

}
