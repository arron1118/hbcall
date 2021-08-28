<?php


namespace app\common\controller;


use think\facade\Session;
use think\facade\View;
use think\facade\Config;

class PortalController extends \app\BaseController
{

    protected $view = null;

    protected $userInfo = null;

    protected $returnData = [
        'code' => 0,
        'msg' => 'Unknown error',
        'data' => []
    ];

    protected $title = '';

    public function initialize()
    {
        $this->returnData['msg'] = lang('Unknown error');

        $this->view = View::instance();

        // 用户登录状态
        $this->userInfo = $this->getUserInfo();
        $this->view->assign('user', $this->userInfo);

        // SEO
        $this->view->assign('site', Config::get('site'));
    }

    protected function getUserInfo()
    {
        return Session::get('user');
    }

    public function isLogin()
    {
        return Session::has('user');
    }

}
