<?php


namespace app\common\controller;

use app\common\model\User;
use think\facade\Event;
use think\facade\Session;
use think\facade\View;

class HomeController extends \app\BaseController
{
    protected $middleware = [\app\home\middleware\Check::class];

    protected $view = null;

    protected $userInfo = null;

    protected $returnData = [
        'code' => 0,
        'msg' => 'Unknown error',
        'data' => []
    ];

    // 小号
    protected $axb_number = '18426190532';

    protected function initialize()
    {
        $this->returnData['msg'] = lang('Unknown error');

        $this->view = View::instance();
//        $this->view->engine()->layout('layout');

        $this->userInfo = User::with(['company', 'userXnumber'])->findOrEmpty(Session::get('user.id'));
//        dump($this->userInfo->company->corporation);
        if (!$this->userInfo->isEmpty() && (!$this->userInfo->getData('status') || !$this->userInfo->company->getData('status')) && ($this->request->action() !== 'logout')) {
            showAlert(lang('Account is locked'), [
                'end' => 'function () {
                    location.href = "' . url('/index/logout') . '";
                }'
            ]);
        }

        if ($this->userInfo->isEmpty() && !in_array($this->request->action(), ['logout', 'login'])) {
            showAlert(lang('Account is locked'), [
                'end' => 'function () {
                    location.href = "' . url('/index/logout') . '";
                }'
            ]);
            exit();
        }

        $this->view->assign('user', $this->userInfo);
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
