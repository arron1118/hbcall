<?php


namespace app\home\controller;

use app\common\model\User as UserModel;
use app\common\traits\UserTrait;
use think\facade\Session;

class User extends \app\common\controller\HomeController
{
    use UserTrait;

    public function profile()
    {
        if ($this->request->isPost()) {
            $username = trim($this->request->param('username'));
            $realname = trim($this->request->param('realname'));
            $phone = trim($this->request->param('phone'));
            if (empty($username)) {
                $this->returnData['msg'] = '请输入昵称';
                return json($this->returnData);
            }

            if (UserModel::where([['id', '<>', $this->userInfo['id']], ['username', '=', $username]])->count() > 0) {
                $this->returnData['msg'] = '昵称已存在';
                return json($this->returnData);
            }

            if (UserModel::where([['id', '<>', $this->userInfo['id']], ['phone', '=', $phone]])->count() > 0) {
                $this->returnData['msg'] = '手机号已存在';
                return json($this->returnData);
            }

            $this->userInfo->username = $username;
            $this->userInfo->realname = $realname;
            $this->userInfo->phone = $phone;
            $this->userInfo->save();
            Session::set('user.username', $username);
            Session::set('user.realname', $realname);
            Session::set('user.phone', $phone);

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作成功';
            return json($this->returnData);
        }
        $this->view->assign('userProfile', $this->userInfo);
        return $this->view->fetch();
    }
}
