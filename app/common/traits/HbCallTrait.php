<?php

namespace app\common\traits;

use app\common\model\CallHistory;
use app\common\model\User;
use Curl\Curl;
use think\facade\Config;

trait HbCallTrait
{
    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        if ($this->userType === 'company') {
            $this->returnData['msg'] = '暂不开通管理员账号呼叫';
            return json($this->returnData);
        }

        if (time() >= strtotime($this->stopStartDateTime) && time() <= strtotime($this->stopEndDateTime)) {
            $this->returnData['msg'] = "由于线路临时升级，呼叫系统 将在{$this->stopStartDateTime}至{$this->stopEndDateTime} 共计两小时暂停服务,给大家带来不便，非常抱歉。感谢大家的支持！";
            return json($this->returnData);
        }

        $mobile = $this->request->param('mobile', '');
        $mobile = trim($mobile);

        // 试用账号到期后无法拨号
        if ($this->userInfo->company->getData('is_test') && time() > $this->userInfo->company->getData('test_endtime')) {
            $this->returnData['msg'] = lang('At the end of the test time, please contact the administrator to recharge and try again');
            return json($this->returnData);
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            return json($this->returnData);
        }

        // 不是试用账号且欠费无法拨号
        if (!$this->userInfo->company->getData('is_test') && $this->userInfo->company->getData('balance') <= 0) {
            $this->returnData['msg'] = lang('您的余额已经不足，为了不影响呼叫，请联系管理员及时充值！');
            return json($this->returnData);
        }

        if (!$this->userInfo->userXnumber) {
            $this->returnData['msg'] = lang('If a small number is not assigned, contact your administrator to assign a small number and try again');
            return json($this->returnData);
        }

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = lang('Please enter the correct mobile phone number');
            return json($this->returnData);
        }

        if ($this->userInfo->phone === '') {
            $this->returnData['msg'] = '请先在个人资料中填写手机号';
            return json($this->returnData);
        }
        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $this->userInfo->phone,
            'telB' => $mobile,
            'telX' => $this->userInfo->userXnumber->numberStore->number
        ]);
        $response = json_decode($curl->response, true);

        if ($response['code'] === 1000) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = $this->userInfo->id;
            $CallHistory->username = $this->userInfo->username;
            $CallHistory->company_id = $this->userInfo->company_id;
            $CallHistory->company = $this->userInfo->company->corporation;
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->caller_number = $this->userInfo->phone;
            $CallHistory->axb_number = $this->userInfo->userXnumber->numberStore->number;
            $CallHistory->called_number = $mobile;
            $CallHistory->createtime = time();
            $CallHistory->device = $this->agent->device();
            $CallHistory->platform = $this->agent->platform();
            $CallHistory->platform_version = $this->agent->version($this->agent->platform());
            $CallHistory->browser = $this->agent->browser();
            $CallHistory->browser_version = $this->agent->version($this->agent->browser());
            $CallHistory->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '拨号成功';
            $this->returnData['data'] = [
                'xNumber' => $this->userInfo->userXnumber->numberStore->number,
                'mobile' => $mobile,
            ];
        } else {
            $this->returnData['msg'] = $response['msg'];
        }

        return json($this->returnData);
    }
}
