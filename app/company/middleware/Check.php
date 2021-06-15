<?php


namespace app\company\middleware;


use think\facade\Session;

class Check
{
    public function handle($request, \Closure $next)
    {
        if (!Session::has('company') && $request->action() !== 'login') {
            return redirect(url('company/index/login'));
        }

        return $next($request);
    }

}
