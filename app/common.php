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
    $current_year = date('Y');  // 当年
    $next_year = $current_year + 1;
    $current_month = date('m'); // 当月
    $current_day = date('d');   // 当天
    $year_first_month = '-01';  // 每年的第一月
    $first_day = '-01'; // 每月的第一天
    $year_last_month = '-12';
    $year_last_day = '-31';
    $today_start = strtotime('today');  // 当天起始
    $today_end = strtotime('tomorrow') - 1; // 当天结束
    $yesterdayStart = strtotime('yesterday');  // 昨日起始
    $yesterdayEnd = strtotime('today') - 1;    // 昨日结束
    $next_month = $current_month + 1;   // 下月
    $current_month_start = strtotime($current_year . '-' . $current_month . $first_day);    // 当月起始
    // 当月结束
    if ($next_month > 12) {
        $current_month_end = strtotime($next_year . $year_first_month . $first_day) - 1;
    } else {
        $current_month_end = strtotime($current_year . '-' . $next_month . $first_day) - 1;
    }
    $current_year_start = strtotime($current_year . $year_first_month . $first_day);    // 当年起始
    $current_year_end = strtotime($next_year . $year_first_month . $first_day); // 当年结束


    $expenseModel = \app\common\model\Expense::class;
    // 当日消费
    $current_day_cost = $expenseModel::whereBetween('createtime', [$today_start, $today_end]);
    // 昨日消费
    $yesterday_cost = $expenseModel::whereBetween('createtime', [$yesterdayStart, $yesterdayEnd]);
    // 本月消费
    $current_month_cost = $expenseModel::whereBetween('createtime', [$current_month_start, $current_month_end]);
    // 当年消费
    $current_year_cost = $expenseModel::whereBetween('createtime', [$current_year_start, $current_year_end]);
    if ($company_id > 0) {
        $current_day_cost->where('company_id', '=', $company_id);
        $yesterday_cost->where('company_id', '=', $company_id);
        $current_month_cost->where('company_id', '=', $company_id);
        $current_year_cost->where('company_id', '=', $company_id);
    }

    if ($user_id > 0) {
        $current_day_cost->where('user_id', '=', $user_id);
        $yesterday_cost->where('user_id', '=', $user_id);
        $current_month_cost->where('user_id', '=', $user_id);
        $current_year_cost->where('user_id', '=', $user_id);
    }

    $current_day_cost = $current_day_cost->sum('cost');
    $yesterday_cost = $yesterday_cost->sum('cost');
    $current_month_cost = $current_month_cost->sum('cost');
    $current_year_cost = $current_year_cost->sum('cost');

    return [
        'current_day_cost' => $current_day_cost,
        'yesterday_cost' => $yesterday_cost,
        'current_month_cost' => $current_month_cost,
        'current_year_cost' => $current_year_cost
    ];
}
