<?php


namespace app\admin\middleware;


use think\facade\Session;

class Check
{
    public function handle($request, \Closure $next)
    {
        if (!Session::has('admin') && $request->action() !== 'login') {
            return redirect((string) url('/index/login'));
        }

        return $next($request);
    }

}
