<?php

namespace app\common\traits;

use Jenssegers\Agent\Agent;

trait PaymentTrait
{
    protected function createOrder($amount, $payType = 1): array
    {
        $orderNo = $this->request->param('payno', '');
        $title = '喵头鹰呼叫系统 - 余额充值';
        if (!$orderNo) {
            $data = $this->addOrder($amount, $payType);
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

    protected function addOrder($amount, $payType, $title = '余额充值')
    {
        $orderNo = getOrderNo();
        $order = [
            'payno' => $orderNo,
            'company_id' => $this->userInfo->id,
            'corporation' => $this->userInfo->corporation,
            'title' => $title,
            'amount' => $amount,
            'pay_type' => $payType,
            'create_time' => time(),
            'platform' => $this->request->isMobile() ? 'app' : 'pc',
            'user_agent' => $this->request->header('user-agent')
        ];
        $this->model->save($order);

        return ['orderNo' => $orderNo, 'title' => $title];
    }
}
