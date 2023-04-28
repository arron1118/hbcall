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
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $operate = $this->request->param('operate', '');
            $keyword = $this->request->param('keyword', '');
            $map = [];

            $this->returnData['count'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->order('id desc, logintime desc')
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

            if ($this->model::where('id <> ' . $params['id'] . ' and username = "' . $params['username'] . '"')->count() > 0) {
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

    public function edit($id = 0)
    {
        if (!$id) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $admin = $this->model::find($id);

        if (!$admin) {
            $this->returnData['msg'] = lang('No data was found');
            return json($this->returnData);
        }

        if ($this->model::where('id <> ' . $params['id'] . ' and username = "' . $params['username'] . '"')->count() > 0) {
            $this->returnData['msg'] = '用户已经存在';
            return json($this->returnData);
        }

        if ($params['password'] !== $admin->password) {
            $admin->password = getEncryptPassword(trim($params['password']), $admin->salt);
        }

        if ($this->request->isPost()) {
            $this->returnData['msg'] = '更新失败';

            if ($admin->save($this->request->param())) {
                $this->returnData['msg'] = '更新成功';
                $this->returnData['code'] = 1;
            }
        }

        return json($this->returnData);
    }
}
