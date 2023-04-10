<?php

namespace app\common\middleware;

use app\admin\model\Admin;
use app\common\model\User;
use app\common\model\Company;

class Check
{
    protected $noNeedLogin = ['login'];

    protected $module = null;

    protected $model = null;

    protected $token = null;

    public function handle($request, \Closure $next)
    {
        $this->module = app('http')->getName();
        $this->token = $request->cookie('hbcall_' . $this->module . '_token');

        $this->model = User::class;
        if ($this->module === 'admin') {
            $this->model = Admin::class;
        } elseif ($this->module === 'company') {
            $this->model = Company::class;
        }

        if (!in_array($request->action(), $this->noNeedLogin, true) && !$this->checkToken()) {
            if ($request->isAjax()) {
                return json(['url' => (string) url('/user/login', [], true, true), 'code' => 5003]);
            }

            return redirect((string) url('/user/login'));
        }

        return $next($request);
    }

    protected function checkToken()
    {
        if ($this->token) {
            $userInfo = $this->model::getByToken($this->token);

            if ($this->module === 'home' && $userInfo && $userInfo->getData('is_test')
                && $userInfo->getData('test_endtime') < time()) {
                $userInfo->status = 0;
                $userInfo->save();
            }

            return !(!$userInfo || !$userInfo->getData('status') || $userInfo->token_expire_time < time());
        }

        return false;
    }
}
