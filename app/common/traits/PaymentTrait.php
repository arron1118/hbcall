<?php

namespace app\common\traits;

trait PaymentTrait
{
    protected function createOrder($amount): array
    {
        $orderNo = $this->request->param('payno', '');
        $title = '余额充值';
        if (!$orderNo) {
            $orderNo = getOrderNo();
            $order = [
                'payno' => $orderNo,
                'company_id' => $this->userInfo->id,
                'corporation' => $this->userInfo->corporation,
                'title' => $title,
                'amount' => $amount,
                'pay_type' => 2,
                'create_time' => time(),
            ];
            $this->model->save($order);
        }

        return ['orderNo' => $orderNo, 'amount' => $amount, 'title' => '喵头鹰呼叫系统 - ' . $title];
    }
}
