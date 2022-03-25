<?php

namespace app\common\traits;

trait PaymentTrait
{
    protected function createOrder($amount, $payType = 1): array
    {
        $orderNo = $this->request->param('payno', '');
        $title = '喵头鹰呼叫系统 - 余额充值';
        if (!$orderNo) {
            $orderNo = getOrderNo();
            $order = [
                'payno' => $orderNo,
                'company_id' => $this->userInfo->id,
                'corporation' => $this->userInfo->corporation,
                'title' => $title,
                'amount' => $amount,
                'pay_type' => $payType,
                'create_time' => time(),
            ];
            $this->model->save($order);
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
}
