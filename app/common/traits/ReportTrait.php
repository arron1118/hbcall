<?php

namespace app\common\traits;

use app\common\model\Company;
use think\facade\Db;

trait ReportTrait
{
    public function index()
    {
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('companies', $company);
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }

        return $this->view->fetch('common@report/index');
    }

    public function getReport()
    {
        $startDate = $this->request->param('startDate', '');
        $endDate = $this->request->param('endDate', '');
        $companyId = (int) $this->request->param('company_id', $this->module === 'company' ? $this->userInfo->id : 0);
        $whereCompany = ' 1 = 1 ';
        $where = ' ';

        if ($companyId > 0) {
            $whereCompany = ' company_id = ' . $companyId;
        }

        if ($startDate && !$endDate) {
            $where .= ' and createtime > ' . strtotime($startDate);
        }

        if (!$startDate && $endDate) {
            $where .= ' and createtime < ' . strtotime($endDate);
        }

        if ($startDate && $endDate) {
            $where .= ' and (createtime between ' . strtotime($startDate) . ' and ' . strtotime($endDate) . ')';
        }

        $prefix = config('database.connections.mysql.prefix');
        $userTable = $prefix . 'user';
        $companyTable = $prefix . 'company';
        $callHistoryTable = $prefix . 'call_history';
        $expenseTable = $prefix . 'expense';
        $sql = <<<SQL
select u.id, u.username, c.corporation, total, total1, total2, duration, cost
from {$userTable} u
         left join {$companyTable} c on u.company_id=c.id
         left join (select count(id) as total, user_id
                    from {$callHistoryTable} h where 1 = 1  {$where}
                    group by user_id) ch on u.id = ch.user_id
left join (select count(id) as total1, user_id from {$callHistoryTable} where call_duration > 0 {$where} group by user_id) lch on lch.user_id=u.id
left join (select count(id) as total2, user_id from {$callHistoryTable} where call_duration > 30 {$where} group by user_id) gch on gch.user_id=u.id
left join (select sum(duration) as duration, user_id from {$expenseTable} where 1=1 {$where} group by user_id) dr on dr.user_id=u.id
left join (select sum(cost) as cost, user_id from {$expenseTable} where 1=1 {$where} group by user_id) expense on expense.user_id=u.id
where {$whereCompany} and total > 0
# order by total desc
SQL;

        $this->returnData['code'] = 1;
        $this->returnData['data'] = Db::query($sql);
        $this->returnData['msg'] = 'success';

        return json($this->returnData);
    }
}
