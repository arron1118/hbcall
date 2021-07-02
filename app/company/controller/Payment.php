<?php


namespace app\company\controller;

use chillerlan\QRCode\QRCode;
use think\facade\Config;
use think\facade\Session;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exceptions\GatewayException;


class Payment extends \app\common\controller\CompanyController
{

    public function initialize() {
        parent::initialize();

        $this->model = new \app\company\model\Payment();
    }

    public function index()
    {
        /**
         * 检查微信订单是否已支付
         */
        $notpay = $this->model->where(['company_id' => Session::get('company.id'), 'payment_no' => '', 'pay_type' => 1])->select();
        foreach ($notpay as $key => $value) {
            $data = Pay::wechat(Config::get('wxpay'))->find(['out_trade_no' => $value->payno]);
            if ($data->trade_state === 'SUCCESS') {
                $mt = mktime(
                    substr($data->time_end, 8, 2),
                    substr($data->time_end, 10, 2),
                    substr($data->time_end, 12, 2),
                    substr($data->time_end, 4, 2),
                    substr($data->time_end, 6, 2),
                    substr($data->time_end, 0, 4)
                );
                $paymentModel = $this->model->where('payno', $data->out_trade_no)->find();
                $paymentModel->pay_time = $mt;
                $paymentModel->payment_no = $data->transaction_id;
                $paymentModel->status = 1;
                $paymentModel->save();
            }
        }

        /**
         * 检查支付宝订单是否已支付
         */
        $notalipay = $this->model->where(['company_id' => Session::get('company.id'), 'payment_no' => '', 'pay_type' => 2])->select();
        foreach ($notalipay as $key => $value) {
            try {
                $data = Pay::alipay(Config::get('alipay'))->find(['out_trade_no' => $value->payno]);
                $paymentModel = $this->model->where('payno', $data->out_trade_no)->find();
                $paymentModel->pay_time = strtotime($data->send_pay_date);
                $paymentModel->payment_no = $data->trade_no;
                $paymentModel->status = 1;
                $paymentModel->save();
            } catch (GatewayException $e) {
                $response = $e->raw['alipay_trade_query_response'];
                if ($response['code'] === '40004' && $response['sub_code'] === 'ACQ.TRADE_NOT_EXIST') {
//                    $value->delete();
                }
            }
        }

        return $this->view->fetch();
    }

    /**
     * 获取订单列表
     * @return \think\response\Json
     */
    public function getOrderList()
    {
        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $total = $this->model->count();

            $historyList = $this->model->order('id DESC')->limit(($page - 1) * $limit, $limit)->select();
            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }
    }

    /**
     * 检测订单
     * @return \think\response\Json
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function checkOrder()
    {
        $payno = $this->request->param('payno');
        $res = Pay::wechat(Config::get('wxpay'))->find(['out_trade_no' => $payno]);
        return json($res);
    }

    /**
     * 支付
     * @return \think\response\Json
     */
    public function wxpay()
    {
        $amount = (float) $this->request->param('amount', 0);
        if ($amount <= 0) {
            return json(['code' => 0, 'msg' => '请输入正确的金额']);
        }

        $orderNo = $this->request->param('payno', '');
        $title = '余额充值';

        if (!$orderNo) {
            $orderNo = getOrderNo();
            $order = [
                'payno' => $orderNo,
                'company_id' => Session::get('company.id'),
                'title' => $title,
                'amount' => $amount,
                'pay_type' => 1,
                'create_time' => time(),
            ];
            $this->model->save($order);
        }

        $wxOrder = [
            'out_trade_no' => $orderNo,
            'total_fee' => $amount * 100, // **单位：分**
            'body' => '喵头鹰呼叫系统 - 余额充值',
        ];

        $pay = Pay::wechat(Config::get('wxpay'))->scan($wxOrder);
        $qr = (new QRCode())->render($pay->code_url);
//        echo '<img src="' . $qr->render($pay->code_url) . '" />';
        $this->view->assign('payno', $orderNo);
        $this->view->assign('qr', $qr);
        return $this->view->fetch();
    }

    public function alipay()
    {
        $amount = (float) $this->request->param('amount', 0);
        if ($amount <= 0) {
            return json(['code' => 0, 'msg' => '请输入正确的金额']);
        }

        $orderNo = $this->request->param('payno', '');
        $title = '余额充值';
        if (!$orderNo) {
            $orderNo = getOrderNo();
            $order = [
                'payno' => $orderNo,
                'company_id' => Session::get('company.id'),
                'title' => $title,
                'amount' => $amount,
                'pay_type' => 2,
                'create_time' => time(),
            ];
            $this->model->save($order);
        }

        $alipayOrder = [
            'out_trade_no' => $orderNo,
            'total_amount' => $amount, // **单位：分**
            'subject' => '喵头鹰呼叫系统 - ' . $title,
        ];

        $alipay = Pay::alipay(Config::get('alipay'))->web($alipayOrder)->send();
//        dump($alipay);
    }

    public function alipayResult()
    {
        return redirect(url('payment/index'));
//        return $this->view->fetch();
    }
}
