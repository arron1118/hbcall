<?php

namespace app\common\traits;

use app\common\model\CallHistory;
use think\facade\Session;

trait CallHistoryTrait
{
    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $companyId = (int) $this->request->param('company_id', Session::get('company.id'));
            $userId = (int) $this->request->param('user_id', 0);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $operate = $this->request->param('operate', '');
            $duration = $this->request->param('duration', '');
            $op = [
                'eq' => '=',
                'gt' => '>',
                'lt' => '<'
            ];
            $map = [
                ['caller_number', '<>', '']
            ];

            if ($companyId) {
                $map[] = ['company_id', '=', $companyId];
            }

            if ($userId > 0) {
                $map[] = ['user_id', '=', $userId];
            }

            if ($startDate && $endDate) {
                $map[] = ['createtime', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            if ($duration !== '' && $operate !== '') {
                $map[] = ['call_duration', $op[$operate], $duration];
            }

            $total = CallHistory::where($map)->count();

            $historyList = CallHistory::with(['expense', 'customer'])
                ->where($map)
                ->order('createtime DESC, id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }

        return json($this->returnData);
    }
}
