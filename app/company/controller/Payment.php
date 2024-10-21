<?php


namespace app\company\controller;

use app\common\traits\PaymentTrait;
use chillerlan\QRCode\QRCode;
use think\facade\Config;
use Yansongda\Pay\Exceptions\Exception;
use Yansongda\Pay\Pay;

class Payment extends \app\common\controller\CompanyController
{
    use PaymentTrait;

    /**
     * 检测订单
     * @return \think\response\Json
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function checkOrder()
    {
        if ($this->request->isPost()) {
            $payno = $this->request->param('payno');
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['data'] = Pay::wechat(Config::get('payment.wxpay'))->find(['out_trade_no' => $payno]);
            return json($this->returnData);
        }
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
        if ($payType === 1) {
            try {
                $pay = Pay::wechat(Config::get('payment.wxpay'))->scan($data);
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '订单创建成功';
                $this->returnData['data'] = [
                    'qr' => (new QRCode())->render($pay->code_url),
                    'payno' => $data['out_trade_no'],
                    'amount' => $amount,
                ];
            } catch (Exception $e) {
                $this->returnData['code'] = $e->getCode();
                $this->returnData['msg'] = $e->getMessage();
            }
            return json($this->returnData);
        } elseif ($payType === 2) {
            try {
            return Pay::alipay(Config::get('payment.alipay.web'))->web($data)->send();
//                return Pay::alipay(Config::get('payment.alipay.web'))->verify();
            } catch (Exception $e) {
//                dump($e);
            }
        }
    }

    public function alipayResult()
    {
        return redirect(url('common@payment/index'));
    }
}
