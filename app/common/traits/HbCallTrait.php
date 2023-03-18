<?php

namespace app\common\traits;

use app\common\model\CallHistory;
use app\common\model\Customer;
use Curl\Curl;
use think\facade\Config;
use think\facade\Log;

trait HbCallTrait
{

    public function makeCall()
    {
        if ($this->userType === 'company') {
            $this->returnData['msg'] = '暂不开通管理员账号呼叫';
            $this->returnApiData();
        }

        if (time() >= strtotime($this->stopStartDateTime) && time() <= strtotime($this->stopEndDateTime)) {
            $this->returnData['msg'] = "由于线路临时升级，呼叫系统 将在{$this->stopStartDateTime}至{$this->stopEndDateTime} 共计两小时暂停服务,给大家带来不便，非常抱歉。感谢大家的支持！";
            $this->returnApiData();
        }

        $mobile = trim($this->params['mobile'] ?? '');
        $customerId = $this->request->param('customerId', 0);
        $customer = Customer::find($customerId);

        // 试用账号到期后无法拨号
        if ($this->userInfo->company->getData('is_test') && time() > $this->userInfo->company->getData('test_endtime')) {
            $this->returnData['msg'] = lang('At the end of the test time, please contact the administrator to recharge and try again');
            $this->returnApiData();
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            $this->returnApiData();
        }

        // 不是试用账号且欠费无法拨号
        if (!$this->userInfo->company->getData('is_test') && $this->userInfo->company->getData('balance') <= 0) {
            $this->returnData['msg'] = lang('您的余额已经不足，为了不影响呼叫，请联系管理员及时充值！');
            $this->returnApiData();
        }

        // 用户试用账号到期后无法拨号
        if ($this->userInfo->getData('is_test')) {
            if (time() > $this->userInfo->getData('test_endtime') || CallHistory::where('user_id', $this->userInfo->id)->count() >= $this->userInfo->limit_call_number) {
                $this->returnData['msg'] = lang('测试时间结束，请联系管理员开通正式账号后再拨打');
                $this->returnApiData();
            }
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            $this->returnApiData();
        }

        if ($this->userInfo->phone === '') {
            $this->returnData['msg'] = '请先在个人资料中填写手机号';
            $this->returnApiData();
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
                    $this->returnApiData();
                }
                $params['caller'] = $this->userInfo->phone;
                $params['called'] = $mobile;
                $params['callerX'] = $this->userInfo->callback_number;
                break;

            default:
                if (!$this->userInfo->userXnumber) {
                    $this->returnData['msg'] = lang('If a small number is not assigned, contact your administrator to assign a small number and try again');
                    $this->returnApiData();
                }

                $params['telA'] = $this->userInfo->phone;
                $params['telB'] = $mobile;
                $params['telX'] = $this->userInfo->userXnumber->numberStore->number;
                break;
        }

        $curl->post(Config::get('hbcall.call_api'), $params);
        $response = json_decode($curl->response, true);

        if ($response) {
            if ($response['code'] == '1000' || $response['code'] == '0000' || $response['code'] == '1003') {
                $CallHistory = new CallHistory();
                $CallHistory->user_id = $this->userInfo->id;
                $CallHistory->username = $this->userInfo->username;
                $CallHistory->company_id = $this->userInfo->company_id;
                $CallHistory->company = $this->userInfo->company->corporation;
                $CallHistory->call_type = $this->userInfo->company->getData('call_type');
                $CallHistory->rate = $this->userInfo->company->rate;
                $CallHistory->caller_number = $this->userInfo->phone;
                $CallHistory->axb_number = $this->userInfo->userXnumber->numberStore->number;
                $CallHistory->called_number = $mobile;
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
                        // call_type = 1
                        $CallHistory->subid = $response['data']['subid'];
                        $this->returnData['data'] = [
                            'xNumber' => $this->userInfo->userXnumber->numberStore->number,
                            'mobile' => $mobile,
                        ];
                        break;
                }

                if ($customer) {
                    $CallHistory->customer = $customer->title;
                }

                $CallHistory->save();

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '拨号成功';
            } else {
                $this->returnData['msg'] = $response['msg'] ?? $response['message'];
            }
        } else {
            $this->returnData['msg'] = '暂时无法呼叫';
        }

        $this->returnApiData();
    }
}
