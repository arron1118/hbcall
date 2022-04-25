<?php

namespace app\common\traits;

use app\common\model\Customer as CustomerModel;

trait CustomerTrait
{
    public function del($id)
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '删除失败';
            if ($id > 0) {
                CustomerModel::destroy(function ($query) use ($id) {
                    $query->where('id', 'in', $id);
                });
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }
}
