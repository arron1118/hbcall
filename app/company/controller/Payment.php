<?php


namespace app\company\controller;

use app\common\traits\PaymentTrait;
use chillerlan\QRCode\QRCode;
use think\facade\Config;
use think\facade\Event;
use think\facade\Log;
use think\facade\Session;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exceptions\GatewayException;

class Payment extends \app\common\controller\CompanyController
{
    use PaymentTrait;

    public function initialize()
    {
        parent::initialize();

        $this->model = new \app\company\model\Payment();
    }

    public function index()
    {
        Event::trigger('Payment');
        return $this->view->fetch();
    }

    /**
     * 获取订单列表
     * @return \think\response\Json
     */
    public function getOrderList()
    {
        if ($this->request->isPost()) {
            $page = (int)$this->request->param('page', 1);
            $limit = (int)$this->request->param('limit', 10);
            $total = $this->model->where('company_id', $this->userInfo->id)->count();

            $historyList = $this->model->where('company_id', $this->userInfo->id)->order('id DESC')->limit(($page - 1) * $limit, $limit)->select();
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
        $res = Pay::wechat(Config::get('payment.wxpay'))->find(['out_trade_no' => $payno]);
        return json($res);
    }

    public function pay()
    {
        $amount = (float) $this->request->param('amount', 0);
        $payType = (int) $this->request->param('payType', 1);
        if ($amount <= 0) {
            return json(['code' => 0, 'msg' => '请输入正确的金额']);
        }

        $data = $this->createOrder($this->userInfo, $amount, $payType);
        if ($payType === 1) {
            $pay = Pay::wechat(Config::get('payment.wxpay'))->scan($data);
            $qr = (new QRCode())->render($pay->code_url);
//        echo '<img src="' . $qr->render($pay->code_url) . '" />';
            $this->view->assign('payno', $data['out_trade_no']);
            $this->view->assign('qr', $qr);
            return $this->view->fetch('payment/wxpay');
        } else if ($payType === 2) {
            return Pay::alipay(Config::get('payment.alipay.web'))->web($data)->send();
        }
    }

    public function alipayResult()
    {
        return redirect(url('payment/index'));
//        return $this->view->fetch();
    }
}
