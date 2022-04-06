<?php


namespace app\api\controller;


use app\common\model\CallHistory;
use app\common\model\Expense;
use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;
use app\common\traits\HbCallTrait;

class HbCall extends \app\common\controller\ApiController
{
    use HbCallTrait;

    protected $noNeedLogin = ['updateCallHistory'];

    protected $stopStartDateTime = '2022-03-14 19:00:00';
    protected $stopEndDateTime = '2022-03-14 21:00:00';

    /**
     * 获取通话记录 (拨通号码的记录)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHistoryList()
    {
        $page = $this->params['page'] ?? 1;
        $limit = $this->params['limit'] ?? 10;
        $date = $this->params['date'] ?? '';
        $map = [];

        if ($this->userType === 'user') {
            $map[] = ['user_id', '=', $this->userInfo->id];
        }

        if ($this->userInfo === 'company') {
            $map[] = ['company_id', '=', $this->userInfo->id];
        }

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
        $historyList = CallHistory::field('id, called_number, createtime, username, call_duration')
            ->where($map)
            ->order('starttime DESC')
            ->limit(($page - 1) * $limit, $limit)
            ->select();
        foreach ($historyList as $key => &$item) {
            $item->expense = Expense::field('duration, rate, cost')
                ->where('call_history_id', $item->id)
                ->findOrEmpty()
                ->toArray();
        }
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '操作成功';
        $this->returnData['data'] = $historyList->toArray();
        $this->returnData['total'] = $total;
        $this->returnApiData();
    }

    /**
     * 更新通话记录
     */
    public function updateCallHistory()
    {
//        return date('Y-m-d H:i:s', 1639122363);
        Event::trigger('CallHistory');
        return 'Done!';
    }
}
