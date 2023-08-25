<?php
/**
 * copyright@Administrator
 * 2023/4/28 0028 11:19
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

class Admin extends \app\common\controller\AdminController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = \app\admin\model\Admin::class;

        if ($this->userInfo->id !== 1) {
            halt('无权限查看');
        }
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $username = $this->request->param('username', '');
            $map = [];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            $this->returnData['count'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->hidden(['password', 'salt'])
                ->append(['status_text'])
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '添加失败';
            $params = $this->request->param();
            $params['salt'] = getRandChar(6);
            $params['password'] = getEncryptPassword(trim($params['password']), $params['salt']);

            if ($this->model::where('username', $params['username'])->count() > 0) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            $admin = new $this->model;
            if ($admin->save($params)) {
                $this->returnData['msg'] = '添加成功';
                $this->returnData['code'] = 1;
            }
        }

        return json($this->returnData);
    }

    public function edit()
    {
        $id = $this->request->param('id', 0);
        if (!$id) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $admin = $this->model::field('id, username, password, salt')->find($id);

        if (!$admin) {
            $this->returnData['msg'] = lang('No data was found');
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $this->returnData['msg'] = '更新失败';
            $params = $this->request->param();

            if ($this->model::where('id <> ' . $id . ' and username = "' . $params['username'] . '"')->count() > 0) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if ($params['password'] !== $admin->password) {
                $newPassword = getEncryptPassword(trim($params['password']), $admin->salt);

                $admin->passwordLogs()->save([
                    'admin_id' => $admin->id,
                    'old_password' => $admin->password,
                    'new_password' => $newPassword,
                ]);

                $admin->password = $newPassword;
            }

            if ($admin->save()) {
                $this->returnData['msg'] = '更新成功';
                $this->returnData['code'] = 1;
            }

            return json($this->returnData);
        }

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = lang('Operation successful');
        $this->returnData['data']['userInfo'] = $admin;

        return json($this->returnData);
    }

    public function updateUser()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '更新失败';
            if ($this->request->has('id')) {
                $param = $this->request->param();
                $user = $this->model::find($param['id']);
                if ($user->save($param)) {
                    $this->returnData['msg'] = '更新成功';
                    $this->returnData['code'] = 1;
                }
            }
        }

        return json($this->returnData);
    }

    public function del()
    {
        if ($this->request->isPost()) {
            $id = (int) $this->request->param('id', 0);
            if (!$id || $id === 1) {
                $this->returnData['msg'] = '请提供正确的参数';
                return json($this->returnData);
            }

            $company = $this->model::find($id);
            if ($company->delete()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            } else {
                $this->returnData['msg'] = '删除失败';
            }
        }

        return json($this->returnData);
    }
}
