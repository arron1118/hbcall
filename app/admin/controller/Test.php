<?php

namespace app\admin\controller;

use think\facade\Db;

class Test extends \app\common\controller\AdminController
{

    public function getColumns()
    {
        $columns = Db::query('show full columns from hbcall_user');
        dump($columns);
    }
}
