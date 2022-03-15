<?php


namespace app\company\controller;


use app\common\model\CallHistory;
use app\common\model\User;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;
use think\facade\Session;
use think\facade\Db;

class HbCall extends \app\common\controller\CompanyController
{

    public function callCenter()
    {
        return $this->view->fetch();
    }

    public function callHistoryList()
    {
        return $this->view->fetch('hbcall/history_list');
    }

    public function getHistoryList()
    {
        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $username = $this->request->param('username', '');
            $userId = (int) $this->request->param('user_id', 0);
            $startDate = $this->request->param('startDate', '');
            $endDate = $this->request->param('endDate', '');
            $operate = $this->request->param('operate', '');
            $duration = $this->request->param('duration', '');
            $op = [
                'eq' => '=',
                'gt' => '>',
                'lt' => '<'
            ];
            $map = [
                ['company_id', '=', $this->userInfo['id']],
//                ['company_id', '=', 15],
                ['caller_number', '<>', '']
            ];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($userId > 0) {
                $map[] = ['user_id', '=', $userId];
            }

            if ($startDate && $endDate) {
                $map[] = ['createtime', 'between', [strtotime($startDate), strtotime($endDate)]];
            }

            if ($duration !== '' && $operate !== '') {
                $map[] = ['call_duration', $op[$operate], $duration];
            }

            $total = CallHistory::where($map)->count();

            $historyList = CallHistory::with(['expense', 'customer'])
                ->where($map)
                ->order('createtime DESC, id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }
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
            return json(['data' => '请输入正确的手机号', 'info' => '温馨提示', 'status' => 0]);
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
