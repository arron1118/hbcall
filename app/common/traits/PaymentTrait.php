<?php

namespace app\common\traits;

use app\company\model\Company;
use think\facade\Db;

trait PaymentTrait
{
    protected function createOrder(Company $company, $amount, $payType = 1): array
    {
        $orderNo = $this->request->param('payno', '');
        $title = '喵头鹰呼叫系统 - 余额充值';
        if (!$orderNo) {
            $data = $this->addOrder($company, $amount, $payType);
            $orderNo = $data['orderNo'];
        }


        if ($payType === 1) {
            return [
                'out_trade_no' => $orderNo,
                'total_fee' => $amount * 100, // **单位：分**
                'body' => $title,
            ];
        } else if ($payType === 2) {
            return [
                'out_trade_no' => $orderNo,
                'total_amount' => $amount, // **单位：分**
                'subject' => $title,
            ];
        }
    }

    protected function addOrder(Company $company, $amount, $payType, $title = '余额充值')
    {
        $orderNo = getOrderNo();
        $order = [
            'payno' => $orderNo,
            'company_id' => $company->id,
            'corporation' => $company->corporation,
            'title' => $title,
            'amount' => $amount,
            'pay_type' => $payType,
            'create_time' => time(),
            'device' => $this->agent->device(),
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
        ];
        $this->model->save($order);

        return ['orderNo' => $orderNo, 'title' => $title];
    }

    /**
     * 获取订单列表
     * @return \think\response\Json
     */
    public function getPaymentList()
    {
        if ($this->userType === 'user') {
            $this->returnData['msg'] = '权限不足，暂时不提供查询数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $corporation = trim($this->request->param('corporation', ''));
            $year = $this->request->param('year', '');
            $month = $this->request->param('month', '');
            $day = $this->request->param('day', '');

            $where = [];
            if ($corporation) {
                $where[] = ['corporation', 'like', '%' . $corporation . '%'];
            }

            if ($year) {
                $where[] = [Db::raw('from_unixtime(create_time, "%Y")'), '=', $year];
            }

            if ($month) {
                $where[] = [Db::raw('from_unixtime(create_time, "%m")'), '=', $month];
            }

            if ($day) {
                $where[] = [Db::raw('from_unixtime(create_time, "%d")'), '=', $day];
            }

            $total = $this->model::where($where)->count();

            $historyList = $this->model::where($where)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            return json(['data' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }

        return json($this->returnData);
    }
}
