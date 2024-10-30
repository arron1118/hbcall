<?php


namespace app\company\controller;

use app\common\traits\PaymentTrait;
use chillerlan\QRCode\QRCode;
use think\facade\Config;
use think\facade\Log;
use Yansongda\Pay\Exceptions\Exception;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Pay;

class Payment extends \app\common\controller\CompanyController
{
    use PaymentTrait;

    /**
     * 检查订单
     * @return \think\response\Json
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     */
    public function checkOrder()
    {
        if ($this->request->isPost()) {
            $payno = $this->request->param('payno');

            try {
                $order = Pay::wechat(Config::get('payment.wxpay'))->find(['out_trade_no' => $payno]);
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = 'success';
                $this->returnData['data'] = $order;
            } catch (InvalidGatewayException|InvalidArgumentException|InvalidSignException $e) {
                Log::error('[订单查询异常] ' . $e->getMessage());
            }
        }

        return json($this->returnData);
    }

    public function pay()
    {
        $amount = (float) $this->request->param('amount', 0);
        $payType = (int) $this->request->param('payType/d', 1);
        $orderNo = $this->request->param('payno', '');

        if ($amount <= 0) {
            $this->returnData['msg'] = '请输入正确的金额';
            return json($this->returnData);
        }

        $data = $this->createOrder($this->userInfo, $amount, $payType, $orderNo);
        try {
            if ($payType === 1) {
                $pay = Pay::wechat(Config::get('payment.wxpay'))->scan($data);
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '订单创建成功';
                $this->returnData['data'] = [
                    'qr' => (new QRCode())->render($pay->code_url),
                    'payno' => $data['out_trade_no'],
                    'amount' => $amount,
                ];
                return json($this->returnData);
            }

            if ($payType === 2) {
                return Pay::alipay(Config::get('payment.alipay.web'))->web($data)->send();
            }
        } catch (InvalidGatewayException|InvalidArgumentException|InvalidSignException $e) {
            Log::error('[支付异常] ' . $e->getMessage());
            $this->returnData['code'] = $e->getCode();
            $this->returnData['msg'] = $e->getMessage();

            return json($this->returnData);
        }
    }

    public function alipayResult()
    {
        return redirect(url('common@payment/index'));
    }
}
