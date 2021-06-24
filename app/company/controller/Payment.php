<?php


namespace app\company\controller;

use chillerlan\QRCode\QRCode;
use think\facade\Config;
use think\facade\Session;
use Yansongda\Pay\Pay;


class Payment extends \app\common\controller\CompanyController
{

    public function initialize() {
        parent::initialize();

        $this->model = new \app\company\model\Payment();
    }

    public function index()
    {
        $res1 = Pay::wechat(Config::get('wxpay'))->find(['out_trade_no' => '202106241624521612005893']);
        $res2 = Pay::wechat(Config::get('wxpay'))->find(['out_trade_no' => '202106241624498668248659']);
        dump($res1);
        dump($res2);
        return $this->view->fetch();
    }

    public function find()
    {
        $res = Pay::wechat(Config::get('wxpay'))->find(['out_trade_no' => '202106241624498668248659']);
        return json($res);
    }

    public function pay()
    {
        $amount = (float) $this->request->param('amount', 0);
        if ($amount <= 0) {
            return json(['code' => 0, 'msg' => '请输入正确的金额']);
        }

        $orderNo = getOrderNo();
        $title = '余额充值';

        $order = [
            'payno' => $orderNo,
            'company_id' => Session::get('company.id'),
            'title' => $title,
            'amount' => $amount,
            'pay_type' => 1,
            'create_time' => time(),
        ];
        $this->model->save($order);

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
}
