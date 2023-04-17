<?php

namespace app\admin\controller;

use app\common\model\NumberStore;

class XnumberStore extends \app\common\controller\AdminController
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function getNumberList()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $this->returnData['count'] = NumberStore::count();
            $this->returnData['data'] = NumberStore::withCount(['userXnumber', 'companyXnumber'])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $number = trim($this->request->param('number'));
            if ($this->checkNumber($number)) {
                $this->returnData['msg'] = '号码已经存在';
                return json($this->returnData);
            }

            if ((new NumberStore())->save($this->request->param())) {
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
            $number = trim($this->request->param('number'));

            if ($this->checkNumber($number)) {
                $this->returnData['msg'] = '号码已经存在';
                return json($this->returnData);
            }

            $num = NumberStore::find($id);
            $num->number = $number;
            if ($num->save()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '修改成功';
            } else {
                $this->returnData['msg'] = '修改失败';
            }
        }

        return json($this->returnData);
    }

    protected function checkNumber($number)
    {
        return NumberStore::getByNumber($number);
    }
}
