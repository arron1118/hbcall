<?php
/**
 * copyright@Administrator
 * 2023/5/22 0022 16:40
 * email:arron1118@icloud.com
 */

namespace app\common\traits;

use app\common\model\CustomerPhoneRecord;

trait CustomerPhoneRecordTrait
{
    public function index()
    {
        return $this->view->fetch('common@customer/customer_phone_record_list');
    }

    public function getPhoneRecordList()
    {
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 15);
        $companyId = $this->request->param('company_id/d', $this->module === 'company' ? $this->userInfo->id : 0);
        $where = [];

        if ($companyId > 0) {
            $where[] = ['company_id', '=', $companyId];
        }

        $this->returnData['msg'] = lang('Operation successful');
        $this->returnData['count'] = CustomerPhoneRecord::where($where)->count();
        $this->returnData['data'] = CustomerPhoneRecord::where($where)
            ->order('id', 'desc')
            ->limit(($page - 1) * $limit, $limit)
            ->select();

        return json($this->returnData);
    }
}
