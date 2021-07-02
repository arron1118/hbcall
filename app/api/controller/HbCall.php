<?php


namespace app\api\controller;


use app\common\model\CallHistory;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;

class HbCall extends \app\common\controller\ApiController
{
    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        if (!$this->isLogin()) {
            $this->returnData['msg'] = '权限不足：未登录';
            return json($this->returnData);
        }

        $mobile = $this->request->param('mobile', '');
        $mobile = trim($mobile);

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = '请输入正确的手机号';
            return json($this->returnData);
        }

        $userInfo = $this->getUserInfo();
        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'mobile' => $mobile,
            'axb_number' => $userInfo['axb_number']
        ]);
        $response = json_decode($curl->response, true);

        if ($response['status']) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = $userInfo['id'];
            $CallHistory->username = $userInfo['username'];
            $CallHistory->company_id = $userInfo['company_id'];
            $CallHistory->company = Company::where(['id' => $userInfo['company_id']])->value('username');
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->axb_number = $response['data']['axb_number'];
            $CallHistory->called_number = $response['data']['mobile'];
            $CallHistory->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '拨号成功';
            $this->returnData['data'] = [
                'axb_number' => $response['data']['axb_number'],
                'mobile' => $response['data']['mobile']
            ];
        } else {
            $this->returnData['msg'] = $response['info'];
            $this->returnData['sub_msg'] = $response['data'];
        }

        return json($this->returnData);
    }

}
