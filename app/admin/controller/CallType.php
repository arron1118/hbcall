<?php
/**
 * copyright@Administrator
 * 2023/10/8 0008 14:50
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

use app\common\model\CallType as CallTypeModel;

class CallType extends \app\common\controller\AdminController
{

    public function index()
    {
        return $this->view->fetch();
    }

    public function getCallTypeList()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);
            $this->returnData['count'] = CallTypeModel::count();
            $this->returnData['data'] = CallTypeModel::order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $name = trim($this->request->param('name'));
            if ($this->checkName($name)) {
                $this->returnData['msg'] = '名称已经存在';
                return json($this->returnData);
            }

            if ((new CallTypeModel())->save($this->request->param())) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '添加成功';
            } else {
                $this->returnData['msg'] = '添加失败';
            }
        }

        return json($this->returnData);
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            $id = (int) $this->request->param('id');
            $name = trim($this->request->param('name'));
            $title = trim($this->request->param('title'));
            $api_url = trim($this->request->param('api_url'));

            if (CallTypeModel::where('id != ' . $id . ' and name = "' . $name . '"')->find()) {
                $this->returnData['msg'] = '名称已经存在';
                return json($this->returnData);
            }

            $callType = CallTypeModel::find($id);
            $callType->name = $name;
            $callType->title = $title;
            $callType->api_url = $api_url;
            if ($callType->save()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '修改成功';
            } else {
                $this->returnData['msg'] = '修改失败';
            }
        }

        return json($this->returnData);
    }

    protected function checkName($name)
    {
        return CallTypeModel::getByName($name);
    }

}
