<?php


namespace app\home\middleware;


use think\facade\Session;

class Check
{

    public function handle($request, \Closure $next)
    {
        if (!Session::get('user') && $request->action() !== 'login') {
            return redirect('home/index/login');
        }

        return $next($request);
    }

}
