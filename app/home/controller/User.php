<?php


namespace app\home\controller;

use app\common\model\User as UserModel;
use app\common\traits\UserTrait;

class User extends \app\common\controller\HomeController
{
    use UserTrait;

    public function profile()
    {
        if ($this->request->isPost()) {
            $realname = trim($this->request->param('realname'));
            $phone = trim($this->request->param('phone'));
            $callback_number = trim($this->request->param('callback_number'));
            if (empty($realname)) {
                $this->returnData['msg'] = '请输入真实姓名';
                return json($this->returnData);
            }

            if (UserModel::where([['id', '<>', $this->userInfo['id']], ['phone', '=', $phone]])->count() > 0) {
                $this->returnData['msg'] = '手机号已存在';
                return json($this->returnData);
            }

            $this->userInfo->realname = $realname;
            $this->userInfo->phone = $phone;
            $this->userInfo->callback_number = $callback_number;
            $this->userInfo->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';
            return json($this->returnData);
        }

        return $this->view->fetch();
    }
}
