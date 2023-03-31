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

        $result = array_map(function ($item) {
            $number = number_format($item, 3);
            $numbers = explode('.', $number);
            $numbers[1] = '<span class="fs-6 text-muted important">' . $numbers[1] . '</span>';
            return $numbers[0] . '.' . $numbers[1];
        }, $cost);

        $result['percentage'] = '0%';
        if ($cost['current_day_cost'] > 0) {
            if ($cost['yesterday_cost'] > 0) {
                $result['percentage'] = number_format(($cost['current_day_cost'] - $cost['yesterday_cost']) / $cost['yesterday_cost'] * 100, 2) . '%';
            } else {
                $result['percentage'] = '100%';
            }
        }

        $this->view->assign($result);
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
        $where = ' 1 = 1 ';

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
select u.id, u.realname, c.corporation, total, total1, total2, duration, cost
from {$userTable} u
         left join {$companyTable} c on u.company_id=c.id
         left join (select count(id) as total, user_id
                    from {$callHistoryTable} h where {$whereCompany} and {$where}
                    group by user_id) ch on u.id = ch.user_id
left join (select count(id) as total1, user_id from {$callHistoryTable} where {$whereCompany} and {$where} and call_duration > 0 group by user_id) lch on lch.user_id=u.id
left join (select count(id) as total2, user_id from {$callHistoryTable} where {$whereCompany} and {$where} and call_duration > 30 group by user_id) gch on gch.user_id=u.id
left join (select sum(duration) as duration, sum(cost) as cost, user_id from {$expenseTable} where {$whereCompany} and {$where} group by user_id) dr on dr.user_id=u.id
where {$whereCompany}
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
                    'value' => CallHistory::where($whereCompany)->where('call_duration_min', 1)->count()
                ],
                [
                    'name' => $this->lang['totalBetweenOneToThree'],
                    'value' => CallHistory::where($whereCompany)->where([
                        ['call_duration_min', '>', 1],
                        ['call_duration_min', '<=', 3]
                    ])->count()
                ],
                [
                    'name' => $this->lang['totalBetweenThreeToFive'],
                    'value' => CallHistory::where($whereCompany)->where([
                        ['call_duration_min', '>', 3],
                        ['call_duration_min', '<=', 5]
                    ])->count()
                ],
                [
                    'name' => $this->lang['totalGtFive'],
                    'value' => CallHistory::where($whereCompany)->where('call_duration_min', '>', 5)->count()
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
            $minute = 10; // 每 10 分钟统计数据
            $where = ' 1 = 1';
            if ($this->module === 'company') {
                $where = ' company_id = ' . $this->userInfo->id;
            }
            $second = 3600 * $hours;
            $limit = 60 * $hours / $minute;

            $sql = <<<SQL
select date_format(datetime, '%H:%i') as datetime,
       max(sum)                             as sum,
       max(duration)                        as duration,
       max(expense)                         as expense
from (SELECT date_format(@date := date_add(@date, interval -{$minute} minute), '%Y-%m-%d %H:%i') datetime,
             0 as                                                                         sum,
             0 as                                                                         duration,
             0 as                                                                         expense
      from (select @date :=
                           date_add(from_unixtime(unix_timestamp() - unix_timestamp() % (60 * {$minute}), '%Y-%m-%d %H:%i'), interval
                                    {$minute} minute)
            from hbcall_call_history ch
            limit {$limit}) temp
      union all
      select from_unixtime(create_time - create_time % (60 * {$minute}), '%Y-%m-%d %H:%i') as datetime,
             count(1)                                                                      as sum,
             sum(call_duration_min)                                                        as duration,
             sum(cost)                                                                     as expense
      from hbcall_call_history
      WHERE {$where} and create_time BETWEEN (unix_timestamp() - {$second}) and unix_timestamp()
      group by datetime) t
group by datetime
order by datetime;
SQL;

            $res = Db::query($sql);
            $this->returnData['data'] = $res;
            $this->returnData['msg'] = 'success';
            $this->returnData['code'] = 1;
        }

        return json($this->returnData);
    }
}
