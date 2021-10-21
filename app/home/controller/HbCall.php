<?php


namespace app\home\controller;


use app\common\model\CallHistory;
use app\company\model\Company;
use Curl\Curl;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\Event;
use think\facade\Session;

class HbCall extends \app\common\controller\HomeController
{

    public function callCenter()
    {
        /*$str = '2021/06/20';
        $strTime = strtotime($str);
        dump((bool)$strTime);
        $next = date('Y-m-d H:i:s', $strTime + 86400 - 1);
        dump($next);*/

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
            $holdertime = $this->request->param('holdertime', '');
            $map = [
                ['user_id', '=', $this->userInfo['id']],
                ['caller_number', '<>', '']
            ];

            $start = strtotime($holdertime);
            if ($start) {
                $end = $start + 86400 - 1;
                $map[] = ['starttime', 'between', [$start, $end]];
            }

            $total = CallHistory::where($map)->count();
            $historyList = CallHistory::where($map)->order('starttime DESC, id DESC')->limit(($page - 1) * $limit, $limit)->select();
            return json(['rows' => $historyList, 'total' => $total, 'msg' => '', 'code' => 1]);
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function makeCall()
    {
        $mobile = $this->request->param('mobile');
        $mobile = trim($mobile);
        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            return json(['data' => '请输入正确的手机号', 'info' => '温馨提示', 'status' => 0]);
        }
        $userInfo = \app\common\model\User::with('axbNumber')->find($this->userInfo['id']);

        if ($userInfo['phone'] === '') {
            return json(['data' => '请先在个人资料中填写手机号', 'info' => '温馨提示', 'status' => 0]);
        }

        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $userInfo['phone'],   // 主叫
            'telB' => $mobile,    // 被叫
            'telX' => $userInfo['number'],   // 小号
        ]);
        $response = json_decode($curl->response, true);

        if ($response['status']) {
            try {
                $CallHistory = new CallHistory();
                $CallHistory->user_id = $userInfo['id'];
                $CallHistory->username = $userInfo['username'];
                $CallHistory->company_id = $userInfo['company_id'];
                $CallHistory->company = Company::where(['id' => $userInfo['company_id']])->value('username');
                $CallHistory->subid = $response['data']['subid'];
                $CallHistory->axb_number = $response['data']['axb_number'];
                $CallHistory->called_number = $response['data']['mobile'];
                $CallHistory->createtime = time();
                $CallHistory->save();
            } catch (DbException $dbException) {

            }
        }

        return json($response);
    }
}
