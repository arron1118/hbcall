<?php

namespace app\common\traits;

use app\common\model\Customer;
use app\common\model\CustomerRecord as RecordModel;

trait CustomerRecordTrait
{

    public function index()
    {
        return $this->view->fetch('common@customer/customer_record_list');
    }

    public function getCustomerRecordList()
    {
        if ($this->request->isPost()) {
            $where = [
                ['customer_id', '=', $this->request->param('customer_id/d', 0)]
            ];

            if ($this->module === 'home') {
                $where[] = ['user_id', '=', $this->userInfo->id];
            }

            $this->returnData['count'] = RecordModel::where($where)->count();
            $this->returnData['data'] = RecordModel::where($where)
                ->order('id', 'desc')
                ->select();

            $this->returnData['msg'] = lang('Operation successful');
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['next_call_time'] = strtotime($param['next_call_time']);
            $param['customer'] = Customer::where('id', $param['customer_id'])->value('title');
            $param['user_id'] = $this->userInfo->id;
            $param['username'] = $this->userInfo->username;
            if (RecordModel::create($param)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '保存成功';
            }

            return json($this->returnData);
        }

        $this->view->assign('customer_id', $this->request->param('customer_id'));
        return $this->view->fetch('common@customer/add_record');
    }

    public function edit($id)
    {
        $record = RecordModel::where('user_id', $this->userInfo->id)->find($id);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['next_call_time'] = strtotime($param['next_call_time']);
            if ($record->save($param)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '保存成功';
            }

            return json($this->returnData);
        }

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '获取成功';
        $this->returnData['data'] = $record;

        return json($this->returnData);
    }

    public function del($id)
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '删除失败';
            if ($id > 0) {
                $userInfo = $this->userInfo;
                RecordModel::destroy(function ($query) use ($id, $userInfo) {
                    $query->where([
                        ['id', '=', $id],
                        ['user_id', '=', $userInfo->id],
                    ]);
                });
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }
    }
}
