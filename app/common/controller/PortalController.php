<?php


namespace app\common\controller;


use app\common\model\User;
use think\facade\Session;
use think\facade\View;
use think\facade\Config;

class PortalController extends \app\BaseController
{
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

        // 用户登录状态
        $this->token = $this->request->cookie('hbcall_user_token');
        if ($this->token) {
            $this->userInfo = User::with(['company', 'userXnumber'])->where('token', $this->token)->find();
        }

        $this->view->assign('user', $this->userInfo);

        // SEO
        $this->view->assign('site', Config::get('site'));

        $this->view->assign([
            'title' => '',
            'keywords' => '',
            'description' => ''
        ]);
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
