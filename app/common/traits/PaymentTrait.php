<?php

namespace app\common\traits;

use app\common\model\Company;
use think\facade\Config;
use think\facade\Event;

trait PaymentTrait
{

    public function index()
    {
        Event::trigger('Payment');
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company);
        }
        return $this->view->fetch('common@payment/index');
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
            $payno = trim($this->request->param('payno', ''));
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $payType = (int) $this->request->param('pay_type', 0);
            $status = (int) $this->request->param('status', -1);
            $companyId = (int) $this->request->param('company_id', $this->module === 'company' ? $this->userInfo->id : 0);

            $where = [];

            if ($companyId > 0) {
                $where[] = ['company_id', '=', $companyId];
            }

            if ($status !== -1) {
                $where[] = ['status', '=', $status];
            }

            if ($payno) {
                $where[] = ['payno', '=', $payno];
            }

            if ($startDate && $endDate) {
                $where[] = ['create_time', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            if ($payType > 0) {
                $where[] = ['pay_type', '=', $payType];
            }

            $total = $this->model::where($where)->count();

            $historyList = $this->model::where($where)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            return json(['rows' => $historyList, 'total' => $total, 'msg' => 'success', 'code' => 1]);
        }

        return json($this->returnData);
    }

    protected function createOrder(Company $company, $amount, $payType = 1, $orderNo = ''): array
    {
        $title = Config::get('app.app_name') . ' - 余额充值';
        if (!$orderNo) {
            $data = $this->addOrder($company, $amount, $payType);
            $orderNo = $data['orderNo'];
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

        return [];
    }

    protected function addOrder(Company $company, $amount, $payType, $title = '余额充值')
    {
        $orderNo = getOrderNo();
        $order = [
            'payno' => $orderNo,
            'company_id' => $company->id,
            'corporation' => $company->corporation,
            'title' => $title,
            'amount' => $amount,
            'pay_type' => $payType,
            'create_time' => time(),
            'device' => $this->agent->device(),
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
        ];
        $this->model->save($order);

        return ['orderNo' => $orderNo, 'title' => $title];
    }
}
