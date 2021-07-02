<?php


namespace app\company\controller;


use app\common\model\CallHistory;
use app\common\model\User;
use Curl\Curl;
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
        /**
         * 获取通话记录  暂时放在这里，后期用定时任务实现
         */
//        Event::trigger('CallHistory');

        return $this->view->fetch('hbcall/history_list');
    }

    public function getHistoryList()
    {

        if ($this->request->isPost()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $holdername = $this->request->param('holdername', '');
            $holdertime = $this->request->param('holdertime', '');
            $map = ['company_id' => $this->userInfo['id']];

            if ($holdername) {
                $map['user_id'] = '';
            }

            $total = CallHistory::where('caller_number != ""')->where($map)->count();

            $historyList = CallHistory::where('caller_number != ""')
                ->where($map)
                ->order('id DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            foreach ($historyList as $val) {
                $val->username = User::where(['id' => $val->user_id])->value('username');
            }

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
        $curl->post('http://call.hbosw.net/API/axbCallApi.aspx', [
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
