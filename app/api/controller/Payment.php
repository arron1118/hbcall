<?php


namespace app\api\controller;

use app\company\model\Company;
use think\facade\Config;
use Yansongda\Pay\Pay;
use app\company\model\Payment as PaymentModel;

class Payment extends \app\common\controller\ApiController
{
    protected $config = [
        'appid' => '', // APP APPID
        'app_id' => 'ww8ee3085852a83f1d', // 公众号 APPID
        'miniapp_id' => '', // 小程序 APPID
        'mch_id' => '1503645201',   // 商务号
        'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',    // 商务号KEY
        'notify_url' => 'http://caller.hbosw.com/api/payment/notify',
        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './cert/apiclient_key.pem', // optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'normal', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => getOrderNo(),
            'total_amount' => '0.01', // **单位：分**
            'subject' => '喵头鹰呼叫系统 - PC场景下单并支付测试',
        ];

        /*$wxpay = Config::get('wxpay');
        dump($wxpay);

        $pay = Pay::wechat(Config::get('wxpay'))->scan($order);
        dump($pay);
        $qr = new QRCode();
        echo '<img src="' . $qr->render($pay->code_url) . '" />';*/
        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
        // 支付

        return Pay::alipay(Config::get('alipay'))->web($order)->send();
    }

    /**
     * 微信支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     */
    public function notify()
    {
        $pay = Pay::wechat(Config::get('wxpay'));

        try{
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

        return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }

    public function alipayNotify()
    {
        $alipay = Pay::alipay(Config::get('alipay'));

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
    protected function updateUserAmount($paymentModel)
    {
        $userInfo = Company::find($paymentModel->company_id);
        $userInfo->balance = (float)$userInfo->balance + (float)$paymentModel->amount;
        $userInfo->save();
    }

    public function alipayReturn()
    {
        dump('支付成功，正在跳转...');
    }
}
