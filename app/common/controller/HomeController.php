<?php


namespace app\common\controller;

use app\common\model\User;
use think\facade\Log;

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
        $logs = [
            'all' => $this->request->all(),
            'header' => $this->request->header()
        ];
        $this->returnData['msg'] = lang('Unknown error');
        $this->token = $this->request->cookie('hbcall_' . $this->module . '_token');
        if ($this->token) {
            $this->userInfo = User::with(['company', 'userXnumber'])
                ->where('token', $this->token)
                ->find();
            $this->userInfo && cookie('balance', $this->userInfo->company->balance);
            $logs['user'] = $this->userInfo;
        }
        Log::info(json_encode($logs));

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
