<?php

namespace app\common\traits;

use app\common\model\CallHistory;
use app\common\model\Company;
use app\common\model\Expense;
use app\common\model\User;
use Grpc\Call;
use think\db\Query;
use think\facade\Db;

trait ReportTrait
{

    protected $lang = [
        'totalCallHistory' => '总记录',
        'totalGtZero' => '接听数',
        'totalGtSixty' => '大于1分钟',
        'totalBetweenZeroAndSixty' => '1分钟内',
        'totalBetweenOneToThree' => '1-3分钟',
        'totalBetweenThreeToFive' => '3-5分钟内',
        'totalGtFive' => '大于5分钟',
        'totalEqZero' => '未接听',
    ];

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

    public function dashboard()
    {
        $cost = [];
        if ($this->module === 'admin') {
            $cost = getCosts();
            $cost['total_payment'] = Company::sum('deposit');
            // 总消费
            $cost['total_cost'] = Company::sum('expense');
        } elseif ($this->module === 'company') {
            $cost = getCosts($this->userInfo->id);
            // 总消费
            $cost['total_cost'] = User::sum('expense');
        }

//        $list = CallHistory::with(['expense', 'company'])->where('id', 258264)->order('id desc')->find();
//        dump($list);
//        dump($list->toArray());

        $this->view->assign($cost);
        return $this->view->fetch('common@index/dashboard');
    }

    public function getTopList()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $field = 'id, expense, call_sum, call_success_sum, call_duration_sum';
            $model = null;
            $where = [];
            if ($this->module === 'company') {
                $model = User::class;
                $field .= ', username';
                $where[] = ['company_id', '=', $this->userInfo->id];
            } elseif ($this->module === 'admin') {
                $model = Company::class;
                $field .= ', corporation as username';
            }

            $this->returnData['data'] = $model::field($field)
                ->where($where)
                ->order('call_sum desc')
                ->limit(5)
                ->select()->toArray();
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '获取成功';
        }

        return json($this->returnData);
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
            $where .= ' and create_time > ' . strtotime($startDate);
        }

        if (!$startDate && $endDate) {
            $where .= ' and create_time < ' . strtotime($endDate);
        }

        if ($startDate && $endDate) {
            $where .= ' and (create_time between ' . strtotime($startDate) . ' and ' . strtotime($endDate) . ')';
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

    public function getDashboardReport()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $whereCompany = [];
            $model = null;
            if ($this->module === 'company') {
                $whereCompany[] = ['company_id', '=', $this->userInfo->id];
                $model = User::class;
            } elseif ($this->module === 'admin') {
                $model = Company::class;
            }

            $result['totalCallHistory'] = $model::where($whereCompany)->sum('call_sum');
            $result['totalCallAndPickUp'] = $model::where($whereCompany)->sum('call_success_sum');
            $result['totalCallAndNoPickUp'] = $result['totalCallHistory'] - $result['totalCallAndPickUp'];
            $result['totalCallDuration'] = $model::where($whereCompany)->sum('call_duration_sum');
            $result['chartData'] = [
                [
                    'name' => $this->lang['totalBetweenZeroAndSixty'],
                    'value' => CallHistory::where($whereCompany)->whereBetween('call_duration', [1, 60])->count()
                ],
                [
                    'name' => $this->lang['totalBetweenOneToThree'],
                    'value' => CallHistory::where($whereCompany)->whereBetween('call_duration', [61, 180])->count()
                ],
                [
                    'name' => $this->lang['totalBetweenThreeToFive'],
                    'value' => CallHistory::where($whereCompany)->whereBetween('call_duration', [181, 300])->count()
                ],
                [
                    'name' => $this->lang['totalGtFive'],
                    'value' => CallHistory::where($whereCompany)->where('call_duration', '>', 300)->count()
                ]
            ];

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '获取成功';
            $this->returnData['data'] = $result;
        }

        return json($this->returnData);
    }

    public function getHoursData()
    {
        if ($this->request->isPost()) {
            $hours = $this->request->param('hours', 7);
            $where = ' 1 = 1';
            if ($this->module === 'company') {
                $where = ' ch.company_id = ' . $this->userInfo->id;
            }

            $sql = <<<SQL
select date_format(t3.datetime, '%m-%d %H:%i') as datetime, max(t3.sum) as sum, max(t3.duration) as duration, max(t3.expense) as expense
from (
         SELECT date_format(@cdate := date_add(@cdate, interval -1 hour), '%Y-%m-%d %H') datetime,
                0 as                                                                     sum,
                0 as                                                                     duration,
                0 as expense
         from (SELECT @cdate := DATE_ADD(date_format(current_timestamp(), '%Y-%m-%d %H'), INTERVAL 1 hour)
               from hbcall_call_history limit {$hours}
              ) t1
         UNION ALL
         select * from (select from_unixtime(temp.create_time, '%Y-%m-%d %H') as datetime,
                               count(*)                                    as sum,
                               sum(duration)            as duration,
                               sum(cost)                                   as expense
                        from (select ch.id, ch.create_time, e.duration, e.cost from hbcall_call_history ch
                                 inner join
                             hbcall_expense e on e.call_history_id = ch.id
                        where {$where} and from_unixtime(ch.create_time, '%Y-%m-%d %H') between date_add(
                                date_format(current_timestamp(), '%Y-%m-%d %H'), interval -{$hours}
                                hour) and date_format(current_timestamp(), '%Y-%m-%d %H')) as temp
                        GROUP BY datetime) temp
     ) t3
where t3.datetime between date_add(date_format(current_timestamp(), '%Y-%m-%d %H'), interval -{$hours}
                                   hour) and date_format(current_timestamp(), '%Y-%m-%d %H')
GROUP BY t3.datetime
order by t3.datetime ;
SQL;

            $res = Db::query($sql);
            $this->returnData['data'] = $res;
            $this->returnData['msg'] = 'success';
            $this->returnData['code'] = 1;
//            return json($this->returnData);
        }

        return json($this->returnData);
    }
}
