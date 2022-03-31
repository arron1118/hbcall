<?php


namespace app\company\controller;


use app\common\model\CallHistory;
use app\common\model\User;
use app\common\traits\CallHistoryTrait;
use Curl\Curl;
use think\facade\Config;
use think\facade\Event;
use think\facade\Session;
use think\facade\Db;

class HbCall extends \app\common\controller\CompanyController
{
    use CallHistoryTrait;

    public function callCenter()
    {
        return $this->view->fetch();
    }

    public function callHistoryList()
    {
        return $this->view->fetch('hbcall/history_list');
    }
}
