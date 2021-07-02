<?php


namespace app\common\controller;

use think\facade\Event;
use think\facade\Session;
use think\facade\View;

class HomeController extends \app\BaseController
{
    protected $middleware = [\app\home\middleware\Check::class];

    protected $view = null;

    protected $userInfo = null;

    // 小号
    protected $axb_number = '18426190532';

    protected function initialize()
    {

        $this->view = View::instance();
//        $this->view->engine()->layout('layout');

        $this->userInfo = Session::get('user');
        $this->view->assign('user', $this->userInfo);
    }


    public function isLogin()
    {
        return Session::has('user');
    }
}
