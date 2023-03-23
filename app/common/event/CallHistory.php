<?php


namespace app\common\event;


use app\common\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use app\common\model\Expense;

/**
 * Class CallHistory
 * 获取通话记录
 * @package app\common\event
 */
class CallHistory
{

    public function handle(Request $request)
    {
        $year = trim($request::param('year', date('Y')));
        $month = trim($request::param('month', date('m')));
        $day = trim($request::param('day', date('d')));
        $limit = trim($request::param('limit/d', 100));
        strlen($month) === 1 && $month = '0' . $month;
        strlen($day) === 1 && $day = '0' . $day;

        $date = $year . '-' . $month . '-' . $day;
        $uid = $request::param('uid', 0);
//        dump('请求时间：' . date('Y-m-d H:i:s'));
//        dump('日期：' . $date);

//        $module = app('http')->getName();
        $time = strtotime($date);
        $endTime = time() - 1800;
        if (date('Y-m-d', time()) !== $date) {
            $endTime = $time + 86400 - 1;
        }
        $map = [
//            ['status', '=', '0'],
//            ['user_id', '=', 133],
//            ['company_id', '=', 38],
            ['sync_at', '=', '0']
        ];
//        if ($module === 'home') {
//            $map[] = ['user_id', '=', Session::get('user.id')];
//        }

        if ($uid) {
            $map[] = ['user_id', '=', $uid];
        }

        $HistoryModel = new \app\common\model\CallHistory();
        $callList = $HistoryModel->where($map);
            // id 为5183后开启
        if (!$uid) {
            $callList->whereBetweenTime('create_time', $time, $endTime);
        }

        $callList = $callList->order('id asc')->limit($limit)->select();
        $returnData = [
            'datetime' => date('Y-m-d H:i:s'),
            'date' => $date,
            'total' => count($callList),
            'success' => 0,
            'error' => 0,
            'successList' => [],
            'errList' => []
        ];
        if (!empty($callList)) {
            $curl = new Curl();
            $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $news = [];
            $date = date('Ymd', $time);

            foreach ($callList as $val) {
                if (!$val->create_time) {
                    continue;
                }

//                $date = date('Ymd', $val->getData('create_time'));
                try {
                    if ($val->call_type === 3) {
                        $curl->get(Config::get('hbcall.dx_record_api'), [
                            'bindId' => $val['subid'],
                        ]);
                        $response = json_decode($curl->response, true);

                        $returnData['successList'][] = [
                            $val->subid,
                        ];
                        if (!is_null($response) && $response['statusCode'] === 200) {
                            ++$returnData['success'];
                            $data = $response['data'];
                            if (!is_null($data) && !empty($data)) {
                                if (!is_array($data)) {
                                    $data = json_decode($data, true);
                                }

                                // 更新通话记录
                                if (isset($data['callId'])) {
                                    $val->callid = $data['callId'];
                                    $val->finish_type = $data['callStatus'];
                                    $val->finish_state = $data['finishStatus'];
                                }

                                $val->starttime = strtotime($data['startTime']);
                                $val->releasetime = strtotime($data['endTime']);
                                $val->call_duration = $data['duration'];
                                $val->record_url = $data['preRecordUrl'];

                                if (!$val->getData('create_time')) {
                                    $val->create_time = strtotime($data['startTime']);
                                }

                                if (!$val->axb_number) {
                                    $val->axb_number = $data['telX'];
                                }

                                if ($data['duration'] > 0) {
                                    // 添加消费记录
                                    $ExpenseModel = Expense::where('call_history_id', $val->id)->findOrEmpty();
                                    if ($ExpenseModel->isEmpty()) {
                                        $company = Company::find($val->company_id);

                                        $ExpenseModel = new Expense();
                                        $ExpenseModel->duration =  ceil($data['duration'] / 60);
                                        $ExpenseModel->rate = $company->rate;
                                        $ExpenseModel->cost = $ExpenseModel->duration * $company->rate;
                                        $ExpenseModel->title = '通话消费';
                                        $ExpenseModel->user_id = $val->user_id;
                                        $ExpenseModel->company_id = $val->company_id;
                                        $ExpenseModel->call_history_id = $val->id;
                                        $ExpenseModel->save();

                                        // 扣费
                                        $company->balance -= $ExpenseModel->cost;
                                        $company->expense += $ExpenseModel->cost;
                                        $company->save();
                                    }
                                }
                            }

                            $val->status = 1;
                            $val->sync_at = time();
                            $val->save();
                        }
                    } else {
                        $curl->post(Config::get('hbcall.record_api'), [
                            'subid' => $val['subid'],
                            'date' => $date
                        ]);
                        $response = json_decode($curl->response, true);

                        $returnData['successList'][] = [
                            $val->subid,
                        ];
                        if (!is_null($response) && (isset($response['code']) && $response['code'] === 1000)) {
                            ++$returnData['success'];
                            $data = $response['data'];
                            if (!is_null($data) && !empty($data)) {
                                if (!is_array($data)) {
                                    $data = json_decode($data, true);
                                }

                                // 更新通话记录
                                if (isset($data['callid'])) {
                                    $val->callid = $data['callid'];
                                    $val->finish_type = $data['finishType'];
                                    $val->finish_state = $data['finishState'];
                                    $val->releasecause = $data['releasecause'];
                                }

                                $val->starttime = strtotime($data['starttime']);
                                $val->releasetime = strtotime($data['releasetime']);
                                $val->call_duration = $data['callDuration'];
                                $val->record_url = $data['recordUrl'];

                                if (!$val->getData('create_time')) {
                                    $val->create_time = strtotime($data['starttime']);
                                }

                                if (!$val->axb_number) {
                                    $val->axb_number = $data['xNumber'];
                                }

                                if ($data['callDuration'] > 0) {
                                    // 添加消费记录
                                    $ExpenseModel = Expense::where('call_history_id', $val->id)->findOrEmpty();
                                    if ($ExpenseModel->isEmpty()) {
                                        $company = Company::find($val->company_id);

                                        $ExpenseModel = new Expense();
                                        $ExpenseModel->duration =  ceil($data['callDuration'] / 60);
                                        $ExpenseModel->rate = $company->rate;
                                        $ExpenseModel->cost = $ExpenseModel->duration * $company->rate;
                                        $ExpenseModel->title = '通话消费';
                                        $ExpenseModel->user_id = $val->user_id;
                                        $ExpenseModel->company_id = $val->company_id;
                                        $ExpenseModel->call_history_id = $val->id;
                                        $ExpenseModel->save();

                                        // 扣费
                                        $company->balance -= $ExpenseModel->cost;
                                        $company->expense += $ExpenseModel->cost;
                                        $company->save();
                                    }
                                }
                            }

                            $val->status = 1;
                            $val->sync_at = time();
                            $val->save();
                        }
                    }
                } catch (\ErrorException $e) {
                    ++$returnData['error'];
                    $returnData['errList'][] = [
                        $val->subid => $e->getMessage()
                    ];
                }
            }

//            var_dump('总共：' . $returnData['total'] . ' 成功：' . $returnData['success'] . ' 失败：' . $returnData['error']);
            return $returnData;

            if (!empty($news)) {
                $HistoryModel->saveAll($news);
            }
        }
    }

}
