<?php


namespace app\api\controller;

use app\common\traits\PaymentTrait;
use app\common\model\Company;
use think\facade\Config;
use think\facade\Db;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Pay;
use app\common\model\Payment as PaymentModel;

class Payment extends \app\common\controller\ApiController
{
    use PaymentTrait;

    protected $noNeedLogin = ['notify', 'alipayNotify', 'alipayReturn'];

    public function initialize()
    {
        parent::initialize();

        $this->model = new PaymentModel();
    }

    public function index()
    {
        $title = config('app.app_name') . ' - PC场景下单并支付测试';
        // 支付宝支付
        $order = [
            'out_trade_no' => getOrderNo(),
            'total_amount' => '0.01', // **单位：分**
            'subject' => $title,
        ];

        // 微信支付
        $order = [
            'out_trade_no' => getOrderNo(),
            'total_fee' => 0.01 * 100, // **单位：分**
            'body' => $title,
        ];

        /*$wxpay = Config::get('payment.wxpay');
        dump($wxpay);

        $pay = Pay::wechat(Config::get('payment.wxpay'))->scan($order);
        dump($pay);
        $qr = new QRCode();
        echo '<img src="' . $qr->render($pay->code_url) . '" />';*/
        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
        // 支付

//        return Pay::alipay(Config::get('payment.alipay.web'))->web($order)->send();
//        return Pay::wechat(Config::get('payment.wxpay'))->web($order)->send();
    }

    /**
     * 微信支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     */
    public function notify()
    {
        $pay = Pay::wechat(Config::get('payment.wxpay'));

        try {
            $data = $pay->verify(); // 是的，验签就这么简单！

            if ($data->result_code === 'SUCCESS') {
                $mt = mktime(
                    substr($data->time_end, 8, 2),
                    substr($data->time_end, 10, 2),
                    substr($data->time_end, 12, 2),
                    substr($data->time_end, 4, 2),
                    substr($data->time_end, 6, 2),
                    substr($data->time_end, 0, 4)
                );
                $paymentModel = PaymentModel::where(['payno' => $data->out_trade_no, 'status' => 0])->findOrEmpty();
//            $paymentModel->startTrans();
                if (!$paymentModel->isEmpty()) {
                    $paymentModel->pay_time = $mt;
                    $paymentModel->payment_no = $data->transaction_id;
                    $paymentModel->status = 1;
                    $paymentModel->save();

                    $this->updateUserAmount($paymentModel);
                }
            }
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $pay->success()->send(); // laravel 框架中请直接 `return $pay->success()`
    }

    public function alipayNotify()
    {
        $alipay = Pay::alipay(Config::get('payment.alipay.web'));

        try {
            $data = $alipay->verify();

            if ($data->trade_status === 'TRADE_SUCCESS') {
                $paymentModel = PaymentModel::where(['payno' => $data->out_trade_no, 'status' => 0])->findOrEmpty();
                if (!$paymentModel->isEmpty()) {
                    $paymentModel->pay_time = strtotime($data->gmt_payment);
                    $paymentModel->payment_no = $data->trade_no;
                    $paymentModel->status = 1;
                    $paymentModel->save();

                    $this->updateUserAmount($paymentModel);
                }
            }
        } catch (\Exception $e) {
        }

        return $alipay->success()->send();
    }

    /**
     * 更新用户余额
     * @param $patmentModel
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function updateUserAmount(PaymentModel $paymentModel)
    {
        $userInfo = Company::find($paymentModel->company_id);
        $userInfo->deposit = (float)$userInfo->deposit + (float)$paymentModel->amount;
        $userInfo->balance = (float)$userInfo->balance + (float)$paymentModel->amount;
        $userInfo->save();

        cookie('balance', $userInfo->balance);
    }

    public function alipayReturn()
    {
        dump('支付成功，正在跳转...');
    }

    /**
     * App下单支付
     * @param float $amount 支付金额
     * @param int $payType 支付类型 [1 微信支付 2 支付宝]
     * @return \Symfony\Component\HttpFoundation\Response|\think\response\Json|void
     */
    public function createAppOrder()
    {
        $amount = (float)($this->params['amount'] ?? 0);
        $payType = (int)($this->params['payType'] ?? 1);
        $orderNo = $this->params['payno'] ?? '';
        if ($amount <= 0) {
            $this->returnData['msg'] = '请输入正确的金额';
            $this->returnApiData();
        }

        if ($this->userType === 'user') {
            $this->returnData['msg'] = '权限不足，暂时不对普通用户开放';
            $this->returnApiData();
        }

        $data = $this->createOrder($this->userInfo, $amount, $payType, $orderNo);

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = 'success';
        if ($payType === 1) {
            $res = Pay::wechat(Config::get('payment.wxpay'))->app($data);
            $this->returnData['data'] = json_decode($res->getContent());
        } elseif ($payType === 2) {
            $res = Pay::alipay(Config::get('payment.alipay.app'))->app($data);
            $this->returnData['data']['alipay'] = $res->getContent();
        }

        $this->returnApiData();
    }

    /**
     * 检查订单
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function checkOrder()
    {
        if ($this->userType === 'user') {
            $this->returnData['msg'] = '权限不足，暂时不提供查询数据';
            $this->returnApiData();
        }

        $payno = $this->params['payno'] ?? '';

        if (!$payno) {
            $this->returnData['msg'] = '请提供正确的订单号';
            $this->returnApiData();
        }

        $payment = \app\common\model\Payment::where('payno', $payno)->find();

        if ($payment) {
            $data = [];
            if ($payment->pay_type === 1) {
                // 检查微信订单是否已支付
                $data = Pay::wechat(Config::get('payment.wxpay'))->find(['out_trade_no' => $payment->payno]);
            } elseif ($payment->pay_type === 2) {
                // 检查支付宝订单是否已支付
                try {
                    $data = Pay::alipay(Config::get('payment.alipay.web'))->find(['out_trade_no' => $payment->payno]);
                } catch (GatewayException $e) {
                    $data = $e->raw['alipay_trade_query_response'];
                }
            }

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = 'success';
            $this->returnData['data'] = $data;
        } else {
            $this->returnData['msg'] = '订单不存在';
        }

        $this->returnApiData();
    }

    /**
     * 获取订单列表
     */
    public function getPaymentList()
    {
        if ($this->userType === 'user') {
            $this->returnData['msg'] = '权限不足，暂时不提供查询数据';
            $this->returnApiData();
        }

        if ($this->request->isPost()) {
            $page = (int) ($this->params['page'] ?? 1);
            $limit = (int) ($this->params['limit'] ?? 10);
            $corporation = trim($this->params['corporation'] ?? '');
            $year = $this->params['year'] ?? '';
            $month = $this->params['month'] ?? '';
            $day = $this->params['day'] ?? '';

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

            $this->returnData['total'] = $this->model::where($where)->count();

            $this->returnData['data'] = $this->model::where($where)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = 'success';
            $this->returnData['code'] = 1;
        }

        $this->returnApiData();
    }
}
