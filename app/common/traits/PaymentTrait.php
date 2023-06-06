<?php

namespace app\common\traits;

use app\common\model\Company;
use think\facade\Config;
use think\facade\Event;

trait PaymentTrait
{

    protected function initialize()
    {
        parent::initialize();

        $this->model = new \app\common\model\Payment();
        $this->view->assign([
            'statusList' => $this->model->getStatusList(),
            'payTypeList' => $this->model->getPayTypeList(),
        ]);
    }

    public function index()
    {
        Event::trigger('Payment');
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company->hidden(['user'])->toArray());
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
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $payno = trim($this->request->param('payno', ''));
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $payType = $this->request->param('pay_type/d', 0);
            $status = $this->request->param('status/d', -1);
            $companyId = $this->request->param('company_id/d', $this->module === 'company' ? $this->userInfo->id : 0);

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

            $this->returnData['count'] = $this->model::where($where)->count();
            $this->returnData['msg'] = lang('Operation successful');
            $this->returnData['data'] = $this->model::where($where)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->append(['pay_type_text', 'status_text'])
                ->select();
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

        $return = [];
        if ($payType === 1) {
            $return = [
                'out_trade_no' => $orderNo,
                'total_fee' => $amount * 100, // **单位：分**
                'body' => $title,
            ];
        } elseif ($payType === 2) {
            $return = [
                'out_trade_no' => $orderNo,
                'total_amount' => $amount, // **单位：分**
                'subject' => $title,
            ];
        }

        return $return;
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
            'device' => $this->agent->device(),
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
            'ip' => $this->request->ip(),
        ];
        $this->model->save($order);

        return ['orderNo' => $orderNo, 'title' => $title];
    }
}
