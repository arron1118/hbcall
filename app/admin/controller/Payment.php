<?php


namespace app\admin\controller;

use chillerlan\QRCode\QRCode;
use think\Collection;
use think\facade\Config;
use think\facade\Db;
use think\facade\Event;
use think\facade\Session;
use think\response\Json;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exceptions\GatewayException;


class Payment extends \app\common\controller\AdminController
{

    public function initialize() {
        parent::initialize();

        $this->model = new \app\company\model\Payment();
    }

    public function index()
    {
//        $data = Pay::alipay(Config::get('payment.alipay'))->find(['out_trade_no' => '202202281646030709550569']);
//        dump($data);
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
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $corporation = trim($this->request->param('corporation', ''));
            $datetime = $this->request->param('datetime', '');

            $where = [];
            if ($corporation) {
                $where[] = ['corporation', 'like', '%' . $corporation . '%'];
            }

            if ($datetime) {
                $where[] = [Db::raw('from_unixtime(create_time, "%Y-%m-%d")'), '=', $datetime];
            }

            $total = $this->model::where($where)->count();

            $historyList = $this->model::where($where)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
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

        $pay = Pay::wechat(Config::get('payment.wxpay'))->scan($wxOrder);
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

        return Pay::alipay(Config::get('payment.alipay'))->web($alipayOrder)->send();
    }

    public function alipayResult()
    {
        return redirect(url('payment/index'));
//        return $this->view->fetch();
    }
}
