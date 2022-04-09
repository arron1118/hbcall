<?php

namespace app\company\controller;

use app\common\model\CustomerRecord as RecordModel;

class CustomerRecord extends \app\common\controller\HomeController
{
    public function index()
    {
        return $this->view->fetch();
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
            if (RecordModel::create($param)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '添加成功';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }
}
