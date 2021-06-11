<?php


namespace app\common\event;


use Curl\Curl;
use think\facade\Session;

/**
 * Class CallHistory
 * 获取通话记录
 * @package app\common\event
 */
class CallHistory
{

    public function handle()
    {
        $callList = \app\common\model\CallHistory::where(['callid' => '', 'user_id' => Session::get('user.id')])->select();
        if (!empty($callList)) {
            $curl = new Curl();
            foreach ($callList as $val) {
                $curl->post('http://call.hbosw.net/API/axbCallRecord.ashx', [
                    'subid' => $val['subid']
                ]);
                $response = json_decode($curl->response, true);
                if ($response['status'] && !empty($response['data'])) {
                    $val->callid = $response['data']['callid'];
                    $val->caller_number = $response['data']['callerNumber'];
                    $val->starttime = strtotime($response['data']['starttime']);
                    $val->releasetime = strtotime($response['data']['releasetime']);
                    $val->call_duration = $response['data']['callDuration'];
                    $val->finish_type = $response['data']['finishType'];
                    $val->finish_state = $response['data']['finishState'];
                    $val->releasecause = $response['data']['releasecause'];
                    $val->record_url = $response['data']['recordUrl'];
                    $val->save();
                }
            }
        }
    }

}
