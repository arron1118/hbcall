<?php

namespace app\common\traits;

trait UserTrait
{
    /**
     * @return \think\response\Json
     */
    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $old_password = trim($this->request->param('old_password'));
            $new_password = trim($this->request->param('new_password'));
            $confirm_password = trim($this->request->param('confirm_password'));

            if (empty($old_password)) {
                $this->returnData['msg'] = '请输入旧密码';
                return json($this->returnData);
            }

            if (empty($new_password)) {
                $this->returnData['msg'] = '请输入新密码';
                return json($this->returnData);
            }

            if (empty($confirm_password)) {
                $this->returnData['msg'] = '请输入确认密码';
                return json($this->returnData);
            }

            if (getEncryptPassword($old_password, $this->userInfo->salt) !== $this->userInfo->password) {
                $this->returnData['msg'] = '输入的旧密码有误';
                return json($this->returnData);
            }

            if ($new_password !== $confirm_password) {
                $this->returnData['msg'] = '输入的确认密码有误';
                return json($this->returnData);
            }
            $this->userInfo->password = getEncryptPassword($confirm_password, $this->userInfo->salt);
            $this->userInfo->token = '';
            $this->userInfo->token_expire_time = 0;

            if ($this->userInfo->save()) {
                $this->returnData['code'] = 1;
                return json(['msg' => lang('Password modification successful, please log in again'), 'code' => 1]);
            }

            $this->returnData['msg'] = lang('Password modification successful, please log in again');

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

}
