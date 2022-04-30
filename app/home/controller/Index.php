<?php

namespace app\home\controller;

use app\common\controller\HomeController;
use app\common\model\Company;
use arron\Random;

class Index extends HomeController
{

    public function index()
    {
        return $this->view->fetch('common@index/index');
    }

    public function getCompanyBalance()
    {
        $balance = Company::where('id', '=', $this->userInfo->company_id)->value('balance');
        return json($balance);
    }

    // 生成用户
    protected function buildUsers()
    {
        $axb_number = [
            '13427445990',
            '13427445989',
            '13427445933',
            '13427445904',
            '13427445850',
            '13427445844',
            '13427445815',
            '13427445802',
            '13427445785',
            '13427445767',
            '13427445747',
            '13427445731',
            '13427445729',
            '13427445722',
            '13427445696',
            '13427445692',
            '13427445683',
            '13427445682',
            '13427445680',
            '13427445636'
        ];

        $data = [];
        foreach ($axb_number as $key => $val) {
            $salt = Random::alnum();
            $data[] = [
                'username' => '慧辰' . (int)($key + 1),
                'password' => getEncryptPassword('123456', $salt),
                'axb_number' => $val,
                'salt' => $salt
            ];
        }
//        $user->saveAll($data);
    }


}
