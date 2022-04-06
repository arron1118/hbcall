<?php

namespace app\common\traits;

use app\company\model\Company;
use think\facade\Db;

trait PaymentTrait
{
    protected function createOrder(Company $company, $amount, $payType = 1): array
    {
        $orderNo = $this->params['payno'] ?? '';
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

        return [];
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
}
