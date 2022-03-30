<?php


namespace app\common\controller;

use app\common\model\User;
use think\facade\Session;

class HomeController extends \app\BaseController
{
//    protected $middleware = [\app\home\middleware\Check::class];

    /**
     * 用户信息
     * @var null
     */
    protected $userInfo = null;

    /**
     * 用户类型
     * @var string
     */
    protected $userType = 'user';

    protected $token = null;

    protected $noNeedLogin = ['login'];

    /**
     * 响应数据
     * @var array
     */
    protected $returnData = [
        'code' => 0,
        'msg' => 'Unknown error',
        'data' => []
    ];

    protected function initialize()
    {
        $this->returnData['msg'] = lang('Unknown error');
        $action = $this->request->action();
        $this->token = $this->request->cookie('hbcall_user_token');

        if (!in_array($action, $this->noNeedLogin)) {
            $this->userInfo = User::with(['company', 'userXnumber'])->where('token', $this->token)->findOrEmpty();

            if (!$this->userInfo->isEmpty() && (!$this->userInfo->getData('status') || !$this->userInfo->company->getData('status')) && ($this->request->action() !== 'logout')) {
                showAlert(lang('Account is locked'), [
                    'end' => 'function () {
                    location.href = "' . url('/index/logout') . '";
                }'
                ]);
            }

            if ($this->userInfo->isEmpty() && !in_array($this->request->action(), ['logout', 'login', 'index'])) {
                showAlert(lang('Account is locked'), [
                    'end' => 'function () {
                    location.href = "' . url('/index/logout') . '";
                }'
                ]);
                exit();
            }

            $this->view->assign('user', $this->userInfo);
        }
    }

    protected function getUserInfo()
    {
        return User::find(Session::get('user.id'));
    }

    public function isLogin()
    {
        return Session::has('user');
    }
}
