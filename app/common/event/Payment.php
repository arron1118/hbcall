<?php

namespace app\common\event;

use think\facade\Config;
use think\facade\Log;
use think\facade\Session;
use Yansongda\Pay\Exceptions\BusinessException;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Pay;

class Payment
{
    public function handle()
    {
        $where = ['status' => 0];
        $cid = Session::get('company.id');
        if ($cid) {
            $where['company_id'] = $cid;
        }

        $notpay = \app\common\model\Payment::where($where)->select();
        foreach ($notpay as $key => $value) {
            try {
                if ($value->pay_type === 1) {
                    // 检查微信订单是否已支付
                    $data = Pay::wechat(Config::get('payment.wxpay'))
                        ->find(['out_trade_no' => $value->payno], 'scan');
                    Log::info('微信订单：' . $data->toJson());

                    if ($data->trade_state === 'SUCCESS') {
                        $mt = mktime(
                            substr($data->time_end, 8, 2),
                            substr($data->time_end, 10, 2),
                            substr($data->time_end, 12, 2),
                            substr($data->time_end, 4, 2),
                            substr($data->time_end, 6, 2),
                            substr($data->time_end, 0, 4)
                        );
                        $value->pay_time = $mt;
                        $value->payment_no = $data->transaction_id;
                        $value->status = 1;
                        $value->save();
                    } elseif ($data->trade_state === 'CLOSED') {
                        $value->status = 2;
                        $value->save();
                    }
                } elseif ($value->pay_type === 2) {
                    // 检查支付宝订单是否已支付
                    $data = Pay::alipay(Config::get('payment.alipay.web'))
                        ->find(['out_trade_no' => $value->payno]);
                    Log::info('支付宝订单：' . json_encode($data));

                    if ($data->trade_status === 'TRADE_SUCCESS') {
                        $value->pay_time = strtotime($data->send_pay_date);
                        $value->payment_no = $data->trade_no;
                        $value->status = 1;
                        $value->save();
                    } elseif ($data->trade_status === 'TRADE_CLOSED') {
                        $value->payment_no = $data->trade_no;
                        $value->status = 2;
                        $value->save();
                    }
                }
            } catch (GatewayException|InvalidConfigException|InvalidArgumentException|BusinessException $e) {
                Log::error('[查询订单异常-' . $value->payno . '] ' . $e->getMessage());
                Log::debug($e->raw);

                if ($value->pay_type === 1) {
                    if ($e->raw['return_code'] === 'SUCCESS' && $e->raw['result_code'] === 'FAIL') {
//                        if ($e->raw['err_code'] === 'ORDERNOTEXIST')
                        $value->status = 2;
                        $value->comment = $e->raw['err_code_des'];
                        $value->save();
                    }
                } elseif ($value->pay_type === 2) {
                    $response = $e->raw['alipay_trade_query_response'];
                    if ($response['code'] === '40004' && $response['sub_code'] === 'ACQ.TRADE_NOT_EXIST') {
                        $value->status = 2;
                        $value->comment = $response['sub_msg'];
                        $value->save();
                    }

                }
            }
        }
    }
}
