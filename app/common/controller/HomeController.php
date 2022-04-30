<?php


namespace app\common\controller;

use app\common\model\User;

class HomeController extends \app\BaseController
{
    protected $middleware = [\app\common\middleware\Check::class];

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
        $this->token = $this->request->cookie('hbcall_user_token');
        if ($this->token) {
            $this->userInfo = User::with(['company', 'userXnumber'])->where('token', $this->token)->find();
        }

        $this->view->assign('user', $this->userInfo);
    }

    protected function getUserInfo()
    {
        return $this->userInfo;
    }

    public function isLogin()
    {
        return $this->userInfo;
    }
}
