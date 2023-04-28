<?php


namespace app\common\controller;

use app\admin\model\Admin;
use think\facade\View;

class AdminController extends \app\BaseController
{
    protected $middleware = [\app\common\middleware\Check::class];

    protected $model = null;

    protected $userInfo = null;

    protected $userType = 'admin';

    protected $returnData = [];

    protected function initialize()
    {
        parent::initialize();

        $this->returnData = [
            'code' => 0,
            'msg' => lang('Unknown error'),
            'data' => []
        ];

        $token = $this->request->cookie('hbcall_admin_token');
        if ($token) {
            $this->userInfo = Admin::where('token', $token)->find();
        }
        $this->view->assign('user', $this->userInfo);
    }

    public function upload()
    {
        $upload = (new \app\common\library\Attachment())->upload('file');

        if (!$upload) {
            $this->returnData['msg'] = '上传失败: 未找到文件';
            return $this->returnData;
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->returnData['msg'] = '上传成功';

        return $this->returnData;
    }

}
