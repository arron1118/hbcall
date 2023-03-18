<?php

namespace app\home\controller;

use app\common\model\CallHistory;
use app\common\model\Customer;
use Curl\Curl;
use think\db\exception\DbException;
use think\facade\Config;
use app\common\traits\CallHistoryTrait;
use think\facade\Log;

class HbCall extends \app\common\controller\HomeController
{
    use CallHistoryTrait;

    protected $stopStartDateTime = '2022-03-14 19:00:00';
    protected $stopEndDateTime = '2022-03-14 21:00:00';

    public function callCenter()
    {
        return $this->view->fetch();
    }

    public function getCallHistory()
    {
        if ($this->request->isPost()) {
            $callHistory = CallHistory::with('customer')
//                ->field('called_number, createtime')
                ->where('user_id', '=', $this->userInfo->id)
                ->order('id', 'desc')
                ->limit(20)
                ->select();
            return json(['rows' => $callHistory, 'msg' => '', 'code' => 1]);
        }

        return json($this->returnData);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function makeCall()
    {
        if (time() >= strtotime($this->stopStartDateTime) && time() <= strtotime($this->stopEndDateTime)) {
            $this->returnData['msg'] = "由于线路临时升级，呼叫系统 将在{$this->stopStartDateTime}至{$this->stopEndDateTime} 共计两小时暂停服务,给大家带来不便，非常抱歉。感谢大家的支持！";
            return json($this->returnData);
        }

        $mobile = trim($this->request->param('mobile', ''));
        $customerId = $this->request->param('customerId', 0);
        $customer = Customer::find($customerId);

        // 不是试用账号且欠费无法拨号
        if (!$this->userInfo->company->getData('is_test') && $this->userInfo->company->getData('balance') <= 0) {
            $this->returnData['msg'] = lang('您的余额已经不足，为了不影响呼叫，请联系管理员及时充值！');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        // 企业试用账号到期后无法拨号
        if ($this->userInfo->company->getData('is_test') && time() > $this->userInfo->company->getData('test_endtime')) {
            $this->returnData['msg'] = lang('At the end of the test time, please contact the administrator to recharge and try again');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        // 用户试用账号到期后无法拨号
        if ($this->userInfo->getData('is_test')) {
            if (time() > $this->userInfo->getData('test_endtime') || CallHistory::where('user_id', $this->userInfo->id)->count() >= $this->userInfo->limit_call_number) {
                $this->returnData['msg'] = lang('测试时间结束，请联系管理员开通正式账号后再拨打');
                $this->returnData['info'] = lang('Tips');
                $this->returnData['status'] = 0;
                return json($this->returnData);
            }
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        if ($this->userInfo->phone === '') {
            $this->returnData['msg'] = '请前往个人资料中填写手机号</a>';
            $this->returnData['info'] = lang('Tips');
            $this->returnData['status'] = 0;
            return json($this->returnData);
        }

        $curl = new Curl();
        $params = [
            'CallType' => $this->userInfo->company->call_type
        ];

        $call_type = $this->userInfo->company->getData('call_type');
        switch ($call_type) {
            case 2:
            case 5:
                $params['caller'] = $this->userInfo->phone;
                $params['called'] = $mobile;
                break;

            case 3:
            case 4:
                if ($this->userInfo->callback_number === '') {
                    $this->returnData['msg'] = '请提供正确的回拨号码后，再重新拨号';
                    $this->returnData['info'] = lang('Tips');
                    $this->returnData['status'] = 0;
                    return json($this->returnData);
                }

                $params['caller'] = $this->userInfo->phone;
                $params['called'] = $mobile;
                $params['callerX'] = $this->userInfo->callback_number;
                break;


            default:
                if (!$this->userInfo->userXnumber) {
                    $this->returnData['msg'] = lang('If a small number is not assigned, contact your administrator to assign a small number and try again');
                    $this->returnData['info'] = lang('Tips');
                    $this->returnData['status'] = 0;
                    return json($this->returnData);
                }

                $params['telA'] = $this->userInfo->phone;
                $params['telB'] = $mobile;
                $params['telX'] = $this->userInfo->userXnumber->numberStore->number;
                break;
        }

        $curl->post(Config::get('hbcall.call_api'), $params);
        Log::info($curl->response);
        $response = json_decode($curl->response, true);

        if ($response) {
            if ($response['code'] == '1000' || $response['code'] == '0000' || $response['code'] == '1003') {
                try {
                    $CallHistory = new CallHistory();
                    $CallHistory->user_id = $this->userInfo->id;
                    $CallHistory->username = $this->userInfo->username;
                    $CallHistory->company_id = $this->userInfo->company_id;
                    $CallHistory->company = $this->userInfo->company->corporation;
                    $CallHistory->call_type = $this->userInfo->company->getData('call_type');
                    $CallHistory->rate = $this->userInfo->company->rate;
                    $CallHistory->caller_number = $this->userInfo->phone;
                    $CallHistory->called_number = $mobile;
                    $CallHistory->axb_number = $this->userInfo->userXnumber->numberStore->number;
                    $CallHistory->customer_id = $customerId;
                    $CallHistory->device = $this->agent->device();
                    $CallHistory->platform = $this->agent->platform();
                    $CallHistory->platform_version = $this->agent->version($this->agent->platform());
                    $CallHistory->browser = $this->agent->browser();
                    $CallHistory->browser_version = $this->agent->version($this->agent->browser());
                    switch ($call_type) {
                        case 2:
                        case 5:
                            $CallHistory->subid = $response['data']['callid'];
                            break;

                        case 3:
                        case 4:
                            $CallHistory->subid = $response['data']['bindId'];
                            $CallHistory->axb_number = $this->userInfo->callback_number;
                            break;

                        default:
                            $CallHistory->subid = $response['data']['subid'];
                            break;
                    }

                    if ($customer) {
                        $CallHistory->customer = $customer->title;
                    }

                    $CallHistory->save();
                } catch (DbException $dbException) {

                }
            }
        } else {
            $response['msg'] = '暂时无法呼叫';
        }

        $response['call_type'] = $call_type;
        return json($response);
    }

    public function downloadTemplate()
    {
        $file = './static/images/customer-example-template.xlsx';
        return download($file, '客户管理.xlsx')->expire(300);
    }
}
