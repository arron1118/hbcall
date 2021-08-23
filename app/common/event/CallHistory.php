<?php


namespace app\common\event;


use app\company\model\Company;
use Curl\Curl;
use think\facade\Config;
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
        $module = app('http')->getName();
        $time = strtotime('2021-08-23');
        $map = [
            ['status', '=', '0']
        ];
        if ($module === 'home') {
            $map[] = ['user_id', '=', Session::get('user.id')];
        }

        $HistoryModel = new \app\common\model\CallHistory();
        $callList = $HistoryModel->where($map)
            // id 为5183后开启
            ->whereBetweenTime('createtime', $time, $time + 86400 - 1)
            ->order('id asc')
            ->limit(100)
            ->select();
        dump($HistoryModel->getLastSql());
        if (!empty($callList)) {
            $curl = new Curl();
            $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $news = [];
            $date = date('Ymd', $time);
            dump($date);

            foreach ($callList as $val) {
                try {
                    $curl->post(Config::get('hbcall.record_api'), [
                        'subid' => $val['subid'],
                        'date' => $date
                    ]);

//                    dump($curl->error_code);
//                    dump($curl->error_message);
                    $response = json_decode($curl->response, true);

//                    dump($val->toArray());
                    dump($response);
                    if (!is_null($response) && $response['status']) {
                        if (!empty($response['data'])) {
                            if (!is_array($response['data'])) {
                                $response['data'] = json_decode($response['data'], true);
                            }

                            // 更新通话记录
                            if (isset($response['data']['callid'])) {
                                $val->callid = $response['data']['callid'];
                                $val->finish_type = $response['data']['finishType'];
                                $val->finish_state = $response['data']['finishState'];
                                $val->releasecause = $response['data']['releasecause'];
                            }

                            $val->caller_number = $response['data']['callerNumber'];
                            $val->starttime = strtotime($response['data']['starttime']);
                            $val->releasetime = strtotime($response['data']['releasetime']);
                            $val->call_duration = $response['data']['callDuration'];
                            $val->record_url = $response['data']['recordUrl'];
                            $val->status = 1;

                            if (!$val->getData('createtime')) {
                                $val->createtime = strtotime($response['data']['starttime']);
                            }

                            $val->save();

                            /*$temp['id'] = $val->id;
                            $temp['callid'] = $response['data']['callid'];
                            $temp['caller_number'] = $response['data']['callerNumber'];
                            $temp['starttime'] = strtotime($response['data']['starttime']);
                            $temp['releasetime'] = strtotime($response['data']['releasetime']);
                            $temp['call_duration'] = $response['data']['callDuration'];
                            $temp['finish_type'] = $response['data']['finishType'];
                            $temp['finish_state'] = $response['data']['finishState'];
                            $temp['releasecause'] = $response['data']['releasecause'];
                            $temp['record_url'] = $response['data']['recordUrl'];
                            $temp['status'] = 1;
                            $news[] = $temp;*/

                            if ($response['data']['callDuration'] > 0) {
                                // 添加消费记录
                                $company = Company::find($val->company_id);
                                $ExpenseModel = new \app\common\model\Expense();
                                $ExpenseModel->duration =  ceil($response['data']['callDuration'] / 60);
                                $ExpenseModel->rate = $company->rate;
                                $ExpenseModel->cost = $ExpenseModel->duration * $company->rate;
                                $ExpenseModel->title = '通话消费';
                                $ExpenseModel->user_id = $val->user_id;
                                $ExpenseModel->company_id = $val->company_id;
                                $ExpenseModel->call_history_id = $val->id;
                                $ExpenseModel->createtime = strtotime($response['data']['starttime']) + 300;
                                $ExpenseModel->save();

                                // 扣费
                                $company->balance = $company->balance - $ExpenseModel->cost;
                                $company->save();
                            }
                        } else {
                            $val->status = 1;
                            $val->save();
                        }
                    }
                } catch (\ErrorException $e) {
                    dump($e);
                }
            }

//            dump($news);
            if (!empty($news)) {
                $HistoryModel->saveAll($news);
            }
        }
    }

}
