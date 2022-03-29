<?php

namespace app\common\traits;

use app\company\model\Company;
use Jenssegers\Agent\Agent;

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
        $agent = new Agent();
        $orderNo = getOrderNo();
        $order = [
            'payno' => $orderNo,
            'company_id' => $company->id,
            'corporation' => $company->corporation,
            'title' => $title,
            'amount' => $amount,
            'pay_type' => $payType,
            'create_time' => time(),
            'platform' => $agent->platform() ?: '',
            'platformVersion' => $agent->version($agent->platform()) ?: '',
            'browser' => $agent->browser() ?: '',
            'browserVersion' => $agent->version($agent->browser()) ?: '',
        ];
        $this->model->save($order);

        return ['orderNo' => $orderNo, 'title' => $title];
    }
}
