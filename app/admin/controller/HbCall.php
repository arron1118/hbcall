<?php


namespace app\admin\controller;


use app\common\model\CallHistory;
use app\common\model\User;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;
use think\facade\Log;
use think\facade\Session;
use think\facade\Db;

class HbCall extends \app\common\controller\AdminController
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
            $datetime = $this->request->param('datetime', '');
            $operate = $this->request->param('operate', '');
            $duration = $this->request->param('duration', '');
            $op = [
                'eq' => '=',
                'gt' => '>',
                'lt' => '<'
            ];
            $map = [
                ['caller_number', '<>', '']
            ];

            if ($username) {
                $map[] = ['username', 'like', '%' . $username . '%'];
            }

            if ($datetime) {
                $daytime = strtotime($datetime);
                $map[] = ['createtime', 'between', [$daytime, $daytime + 86400 - 1]];
            }

            if ($duration !== '' && $operate !== '') {
                $map[] = ['call_duration', $op[$operate], $duration];

            }

            $total = CallHistory::where($map)->count();

            $historyList = CallHistory::with('expense')
                ->where($map)
                ->order('starttime DESC, id DESC')
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
