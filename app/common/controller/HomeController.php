<?php


namespace app\common\controller;

use think\facade\Event;
use think\facade\Session;
use think\facade\View;

class HomeController extends \app\BaseController
{
    protected $middleware = [\app\home\middleware\Check::class];

    protected $view = null;

    // 小号
    protected $axb_number = '18426190532';

    protected function initialize()
    {

        $this->view = View::instance();
//        $this->view->engine()->layout('layout');

        $this->view->assign('user', Session::get('user'));
    }

    public function isLogin()
    {

        if (!Session::get('user') && $this->request->action() !== 'login') {
            return redirect((string) url('index/login'));
        }

        return true;
    }
}
