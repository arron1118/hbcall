<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\common\traits\ReportTrait;
use app\common\model\Company;
use app\common\model\Payment;
use Curl\Curl;
use think\facade\Config;

class Index extends AdminController
{
    use ReportTrait;

    protected $lang = [];

    public function initialize()
    {
        parent::initialize();

        $this->lang = [
            'totalCallHistory' => '总记录',
            'totalGtZero' => '接听数',
            'totalGtSixty' => '大于1分钟',
            'totalBetweenZeroAndSixty' => '1分钟内',
            'totalBetweenOneToThree' => '1-3分钟',
            'totalBetweenThreeToFive' => '3-5分钟内',
            'totalGtFive' => '大于5分钟',
            'totalEqZero' => '未接听',
        ];
    }

    public function index()
    {
        return $this->view->fetch('common@index/index');
    }

    public function dashboard()
    {
        $this->view->assign('totalPayment', Payment::where('status', 1)->sum('amount'));
        $this->view->assign(getCosts());

        return $this->view->fetch('common@index/dashboard');
    }

    public function getTopList()
    {
        if ($this->request->isPost()) {
            $result = Company::withCount(['callHistory' => function ($query, &$alias) {
                $query->where('call_duration', '>', 0);
                $alias = 'callHistory_pickup_count';
            }])
                ->withCount(['callHistory' => function ($query, &$alias) {
                    $query->where('call_duration', '=', 0);
                    $alias = 'callHistory_count';
                }])
                ->withSum(['expense' => 'duration_sum'], 'duration')
                ->field('corporation, expense')
                ->order('callHistory_count', 'desc')
                ->limit(5)
                ->select();
            $this->returnData['code'] = 1;
            $this->returnData['data'] = $result;
            $this->returnData['msg'] = 'success';
            return json($this->returnData);
        }
        return json($this->returnData);
    }

    public function getCallList()
    {
        if ($this->request->isGet()) {
            $this->returnData['code'] = 1;
            return json($this->returnData);
        }

        return json($this->returnData);
    }

}
