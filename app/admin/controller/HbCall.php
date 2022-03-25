<?php


namespace app\admin\controller;


use app\common\model\CallHistory;
use app\common\model\User;
use app\common\traits\CallHistoryTrait;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;
use think\facade\Log;
use think\facade\Session;
use think\facade\Db;

class HbCall extends \app\common\controller\AdminController
{
    use CallHistoryTrait;

    public function callCenter()
    {
        return $this->view->fetch();
    }

    public function callHistoryList()
    {
        $company = $this->getCompanyList();
        $this->view->assign('company', $company);
        return $this->view->fetch('hbcall/history_list');
    }

    public function getCompanyList()
    {
        return Company::field('id, username')->order('id desc, logintime desc')->select();
    }

    public function getUserList($company_id = 0)
    {
        if ($company_id > 0) {
            $userList = User::field('id, username')
                ->where('company_id', $company_id)
                ->order('id desc, logintime desc')
                ->select();
            $this->returnData['code'] = 1;
            $this->returnData['data'] = $userList;
            $this->returnData['msg'] = 'success';

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        $mobile = $this->request->param('mobile');
        $mobile = trim($mobile);
        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            return json(['data' => lang('Please enter the correct mobile phone number'), 'info' => lang('Tips'), 'status' => 0]);
        }
        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'mobile' => $mobile,
            'axb_number' => Session::get('user.axb_number')
        ]);
        $response = json_decode($curl->response, true);
        if ($response['status']) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = Session::get('user.id');
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->axb_number = $response['data']['axb_number'];
            $CallHistory->called_number = $response['data']['mobile'];
            $CallHistory->save();
        }
        return json($response);
    }
}
