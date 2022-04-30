<?php

namespace app\common\traits;

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
                'customer_id' => $this->request->param('customer_id', 0)
            ];

            $total = RecordModel::where($where)->count();

            $res = RecordModel::where($where)
                ->order('id', 'desc')
                ->select();

            $this->returnData['data'] = $res;
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['total'] = $total;

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['next_call_time'] = strtotime($param['next_call_time']);
            if (RecordModel::create($param)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '保存成功';
            }

            return json($this->returnData);
        }

        $this->view->assign('customer_id', $this->request->param('customer_id'));
        return $this->view->fetch('customer/add_record');
    }

    public function edit($id)
    {
        $record = RecordModel::find($id);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['next_call_time'] = strtotime($param['next_call_time']);
            if ($record->save($param)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '保存成功';
            }

            return json($this->returnData);
        }

        $this->view->assign('record', $record);
        return $this->view->fetch('customer/edit_record');
    }

    public function del($id)
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '删除失败';
            if ($id > 0) {
                RecordModel::destroy($id);
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }
    }
}
