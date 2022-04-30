<?php


namespace app\common\event;


use app\common\model\Company;
use Curl\Curl;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;

/**
 * Class CallHistory
 * 获取通话记录
 * @package app\common\event
 */
class CallHistory
{

    public function handle(Request $request)
    {
        $year = $request::param('year', date('Y'));
        $month = $request::param('month', date('m'));
        $day = $request::param('day', date('d'));
        $date = $year . '-' . $month . '-' . $day;
        $uid = $request::param('uid', 0);

        $module = app('http')->getName();
        $time = strtotime($date);
        $map = [
            ['status', '=', '0']
        ];
        if ($module === 'home') {
            $map[] = ['user_id', '=', Session::get('user.id')];
        }

        if ($uid) {
            $map[] = ['user_id', '=', $uid];
        }

        $HistoryModel = new \app\common\model\CallHistory();
        $callList = $HistoryModel->where($map);
            // id 为5183后开启
        if (!$uid) {
            $callList->whereBetweenTime('createtime', $time, $time + 86400 - 1);
        }

        $callList = $callList->order('id asc')->limit(100)->select();
        $returnData = ['total' => count($callList), 'success' => 0, 'error' => 0];
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

                    $response = json_decode($curl->response, true);

                    if (!is_null($response) && $response['code'] === 1000) {
                        $returnData['success'] += 1;
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

                            $val->starttime = strtotime($response['data']['starttime']);
                            $val->releasetime = strtotime($response['data']['releasetime']);
                            $val->call_duration = $response['data']['callDuration'];
                            $val->record_url = $response['data']['recordUrl'];
                            $val->status = 1;

                            if (!$val->getData('createtime')) {
                                $val->createtime = strtotime($response['data']['starttime']);
                            }

                            $val->save();

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
                                $company->expense = $company->expense + $ExpenseModel->cost;
                                $company->save();
                            }
                        } else {
                            $val->status = 1;
                            $val->save();
                        }
                    }
                } catch (\ErrorException $e) {
                    $returnData['error'] += 1;
                    dump($e);
                }
            }

            dump('总共：' . $returnData['total'] . ' 成功：' . $returnData['success'] . ' 失败：' . $returnData['error']);

            if (!empty($news)) {
                $HistoryModel->saveAll($news);
            }
        }
    }

}
