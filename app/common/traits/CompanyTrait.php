<?php

namespace app\common\traits;

use app\common\model\User;
use app\common\model\Company;

trait CompanyTrait
{
    function getCompanyOfUsers($cid = 0, $page = 1, $limit = 15, $order = '')
    {
        $where = [];

        if ($cid) {
            $where[] = ['company_id', '=', $cid];
        }

        if (!$order) {
            $order = 'id desc';
        }

        return User::where($where)->order($order)->limit(($page - 1) * $limit, $limit)->select();
    }

    function getCompanyWithUsers($cid = 0, $page = 1, $limit = 15, $order = '')
    {
        $where = [];

        if ($cid) {
            $where[] = ['company_id', '=', $cid];
        }

        if (!$order) {
            $order = 'id desc';
        }

        return Company::with(['user'])->where($where)->order($order)->limit(($page - 1) * $limit, $limit)->select();
    }
}
