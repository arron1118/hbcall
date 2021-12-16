<?php

namespace app\company\controller;

use think\facade\Db;

class Report extends \app\common\controller\CompanyController
{

    public function index()
    {
        return $this->view->fetch();
    }

    public function getReport()
    {
        $startDate = $this->request->param('startDate', '');
        $endDate = $this->request->param('endDate', '');
        $where = ' ';

        if ($startDate && !$endDate) {
            $where .= ' and createtime > ' . strtotime($startDate);
        }

        if (!$startDate && $endDate) {
            $where .= ' and createtime < ' . strtotime($endDate);
        }

        if ($startDate && $endDate) {
            $where .= ' and (createtime between ' . strtotime($startDate) . ' and ' . strtotime($endDate) . ')';
        }

        $user = $this->userInfo;
        $sql = <<<SQL
select u.id, u.username, c.corporation, total, total1, total2
from hbcall_user u
         left join hbcall_company c on u.company_id=c.id
         left join (select count(id) as total, user_id
                    from hbcall_call_history h where 1=1  {$where}
                    group by user_id) ch on u.id = ch.user_id
left join (select count(id) as total1, user_id from hbcall_call_history where call_duration > 0 {$where} group by user_id) lch on lch.user_id=u.id
left join (select count(id) as total2, user_id from hbcall_call_history where call_duration > 30 {$where} group by user_id) gch on gch.user_id=u.id
where company_id={$user['id']}
order by total desc
SQL;

        $this->returnData['code'] = 1;
        $this->returnData['data'] = Db::query($sql);
        $this->returnData['msg'] = 'success';

        return json($this->returnData);
    }
}