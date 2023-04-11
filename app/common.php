<?php
// 应用公共文件

/**
 * 获取密码加密后的字符串
 * @param string $password 密码
 * @param string $salt 密码盐
 * @return string
 */
function getEncryptPassword($password, $salt = '')
{
    return md5(md5($password) . $salt);
}

/**
 * 随机生成要求位数个字符
 * @param int $length 规定几位字符
 * @return string
 */
function getRandChar($length = 16)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";//大小写字母以及数字
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

/**
 * 生成订单号
 * @return string 返回24位订单号
 */
function getOrderNo()
{
    $t = explode(' ', microtime());
    $m = explode('.', $t[0]);
    return date('Ymd') . $t[1] . substr($m[1], 0, 6);
}

/**
 * 统计当天、当月、当年消费
 * @param int $company_id
 * @param int $user_id
 * @return array
 */
function getCosts($company_id = 0, $user_id = 0)
{
    $where = [
        ['cost', '>', '0'],
    ];

    if ($company_id > 0) {
        $where[] = ['company_id', '=', $company_id];
    }

    if ($user_id > 0) {
        $where[] = ['user_id', '=', $user_id];
    }

    $callHistoryModel = \app\common\model\CallHistory::class;

    return [
        // 当日消费
        'current_day_cost' => $callHistoryModel::where($where)
            ->whereBetweenTime('create_time', strtotime('today'), strtotime('tomorrow'))->sum('cost'),
        // 昨日消费
        'yesterday_cost' => $callHistoryModel::where($where)
            ->whereBetweenTime('create_time', strtotime('yesterday'), strtotime('today'))->sum('cost'),
        // 本月消费
        'current_month_cost' => $callHistoryModel::where($where)
            ->whereBetweenTime('create_time',
                mktime(0, 0, 0, date('m'), 1, date('Y')),
                mktime(0, 0, 0, date('m') + 1, 1, date('Y')))->sum('cost'),
        // 当年消费
        'current_year_cost' => $callHistoryModel::where($where)
            ->whereBetweenTime('create_time',
                mktime(0, 0, 0, 1, 1, date('Y')),
                mktime(0, 0, 0, 1, 1, date('Y') + 1))->sum('cost'),
    ];
}

function showMsg($msg, $options = [])
{
    $o = array_merge($options, ['content' => $msg,
        'icon' => 0,
        'time' => '2500']);
    layerOpen($o);
}

function showAlert($msg, $options = [], callable $callback = null)
{
    layerOpen(array_merge($options, [
        'content' => $msg
    ]));

//    is_callable($callback) && $callback();
}

function layerOpen($options = [])
{
    $defaultOptions = [];
    $options = array_merge($defaultOptions, $options);
    $optionsToJson = json_encode($options);
    $script = getLayuiFiles();
    $html = <<<HTML
{$script}
<script>
    layui.use(['jquery', 'layer'], function () {
        let $ = layui.jquery,
            layer = layui.layer;
        let op = $optionsToJson;
        $.each(op, function (index, item) {
            if (['yes', 'cancel', 'end'].includes(index)) {
                op[index] = eval("(" +item + ")")
            }
        })
        parent.layer.open(op)
    })
</script>
HTML;

    echo $html;
}

function getLayuiFiles()
{
    return <<<SCRIPT
<link href="/static/lib/layui-v2.6.8/css/layui.css" rel="stylesheet" />
<script src="/static/lib/layui-v2.6.8/layui.js"></script>
<script src="/static/js/lay-config.js"></script>
SCRIPT;
}


if (!function_exists('readExcel')) {
    function readExcel($file, $appendColumns = [], $is_repeat_customer = 0)
    {
        $read = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($file->extension()));
        $spreadsheet = $read->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $log = [];

        for ($i = 2; $i <= $highestRow; $i++) {
            $title = $sheet->getCellByColumnAndRow(1, $i)->getValue();
            $phone = $sheet->getCellByColumnAndRow(2, $i)->getValue();
            $province = $sheet->getCellByColumnAndRow(3, $i)->getValue();
            $email = $sheet->getCellByColumnAndRow(4, $i)->getValue();
            $comment = $sheet->getCellByColumnAndRow(5, $i)->getValue();
            $professional = $sheet->getCellByColumnAndRow(6, $i)->getValue();
            $certificate = $sheet->getCellByColumnAndRow(7, $i)->getValue();
            if ($title && $phone) {
                if (!$is_repeat_customer) {
                    $customer = \app\common\model\Customer::where([
                        'phone' => $phone,
                        'company_id' => $appendColumns['company_id'],
                        'type' => $appendColumns['type'],
                    ])->findOrEmpty();

                    if ($customer->isEmpty()) {
                        $a = array_merge($appendColumns, [
                            'title' => trim($title),
                            'phone' => trim($phone),
                            'province' => trim($province ?? ''),
                            'email' => trim($email ?? ''),
                            'comment' => trim($comment ?? ''),
                            'professional' => trim($professional ?? ''),
                            'certificate' => trim($certificate ?? ''),
                        ]);
                        array_push($log, $a);
                    }
                } else {
                    $a = array_merge($appendColumns, [
                        'title' => trim($title),
                        'phone' => trim($phone),
                        'province' => trim($province ?? ''),
                        'email' => trim($email ?? ''),
                        'comment' => trim($comment ?? ''),
                        'professional' => trim($professional ?? ''),
                        'certificate' => trim($certificate ?? ''),
                    ]);
                    array_push($log, $a);
                }
            }
        }

        return $log;
    }
}

function createToken($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function getDateFormatInfo($time)
{
    if (!$time) {
        return '-';
    }

//    if (date('Y-m-d', $time) === date('Y-m-d', time())) {
//        return date('H:i:s', $time);
//    }
//
//    if (date('Y', $time) === date('Y', time())) {
//        return date('m-d H:i:s', $time);
//    }

    return date('Y-m-d H:i:s', $time);
}


