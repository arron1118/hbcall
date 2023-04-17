<?php

namespace app\common\traits;

use app\common\model\CallHistory;
use app\common\model\Company;
use think\db\Query;

trait CallHistoryTrait
{

    public function callHistoryList()
    {
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company->toArray());
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }

        return $this->view->fetch('common@hbcall/history_list');
    }

    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $companyId = (int) $this->request->param('company_id', $this->module === 'company' ? $this->userInfo->id : 0);
            $userId = (int) $this->request->param('user_id', $this->module === 'home' ? $this->userInfo->id : 0);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $operate = $this->request->param('operate', '');
            $duration = $this->request->param('duration', '');
            $caller = $this->request->param('caller', '');
            $phone = $this->request->param('phone', '');
            $op = [
                'eq' => '=',
                'gt' => '>',
                'lt' => '<'
            ];
            $map = [
                ['caller_number', '<>', '']
            ];

            if ($companyId > 0) {
                $map[] = ['company_id', '=', $companyId];
            }

            if ($userId > 0) {
                $map[] = ['user_id', '=', $userId];
            }

            if ($startDate && $endDate) {
                $map[] = ['create_time', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            if ($duration !== '' && $operate !== '') {
                $map[] = ['call_duration', $op[$operate], $duration];
            }

            if ($caller !== '') {
                $map[] = [$caller, 'like', '%' . $phone . '%'];
            }

            $total = CallHistory::where($map)->count();

            $historyList = CallHistory::where($map)
                ->order('create_time DESC, id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }

        return json($this->returnData);
    }
}
