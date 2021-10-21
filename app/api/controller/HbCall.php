<?php


namespace app\api\controller;


use app\common\model\CallHistory;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;

class HbCall extends \app\common\controller\ApiController
{
    /**
     * 拨号
     * @return \think\response\Json
     */
    public function makeCall()
    {
        if (!$this->isLogin()) {
            $this->returnData['code'] = 2;
            $this->returnData['msg'] = '权限不足：未登录';
            return json($this->returnData);
        }

        $mobile = $this->request->param('mobile', '');
        $mobile = trim($mobile);

        if (!$mobile || strlen($mobile) < 11 || !is_numeric($mobile)) {
            $this->returnData['msg'] = '呼叫失败';
            $this->returnData['sub_msg'] = '请输入正确的手机号';
            return json($this->returnData);
        }

        $userInfo = $this->getUserInfo();
        $userInfo = \app\common\model\User::with('axbNumber')->find($userInfo['id']);
        if ($userInfo['phone'] === '') {
            $this->returnData['msg'] = '呼叫失败';
            $this->returnData['sub_msg'] = '请先在个人资料中填写手机号';
            return json($this->returnData);
        }
        $curl = new Curl();
        $curl->post(Config::get('hbcall.call_api'), [
            'telA' => $userInfo['phone'],
            'telB' => $mobile,
            'telX' => $userInfo['xnumber']
        ]);
        $response = json_decode($curl->response, true);

        if ($response['code'] === 1000) {
            $CallHistory = new CallHistory();
            $CallHistory->user_id = $userInfo['id'];
            $CallHistory->username = $userInfo['username'];
            $CallHistory->company_id = $userInfo['company_id'];
            $CallHistory->company = Company::where(['id' => $userInfo['company_id']])->value('username');
            $CallHistory->subid = $response['data']['subid'];
            $CallHistory->caller_number = $userInfo['phone'];
            $CallHistory->axb_number = $userInfo['number'];
            $CallHistory->called_number = $mobile;
            $CallHistory->createtime = time();
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

    /**
     * 获取通话记录 (拨通号码的记录)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHistoryList()
    {
        if (!$this->isLogin()) {
            $this->returnData['msg'] = '权限不足：未登录';
            return json($this->returnData);
        }

        $userInfo = $this->getUserInfo();
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 10);
        $date = $this->request->param('date', '');
        $map = [
            ['user_id', '=', $userInfo['id']],
            ['caller_number', '<>', '']
        ];

        $start = strtotime($date);
        if ($start) {
            $end = $start + 86400 - 1;
            $map[] = ['starttime', 'between', [$start, $end]];
        }

        if ($page <= 0) {
            $page = 1;
        }

        if ($limit <= 0) {
            $limit = 10;
        }

        $total = CallHistory::where($map)->count();
        $historyList = CallHistory::where($map)->order('starttime DESC')->limit(($page - 1) * $limit, $limit)->select();
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '操作成功';
        $this->returnData['data'] = $historyList->visible(['id', 'called_number', 'starttime'])->toArray();
        $this->returnData['total'] = $total;
        return json($this->returnData);
    }

    /**
     * 更新通话记录
     */
    public function updateCallHistory()
    {
        Event::trigger('CallHistory');
        return 'Done!';
    }
}
