<?php
/**
 * copyright@Administrator
 * 2023/9/19 0019 17:28
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

use app\admin\model\AdminSigninLogs;
use app\common\model\CompanySigninLogs;
use app\common\model\UserSigninLogs;

class SigninLogs extends \app\common\controller\AdminController
{

    public function adminLogs()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);

            $this->returnData['count'] = AdminSigninLogs::count();
            $this->returnData['data'] = AdminSigninLogs::with(['admin' => function ($query) {
                return $query->field('id, username');
            }])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function companyLogs()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);

            $this->returnData['count'] = CompanySigninLogs::count();
            $this->returnData['data'] = CompanySigninLogs::with(['company' => function ($query) {
                return $query->field('id, username');
            }])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function userLogs()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);

            $this->returnData['count'] = UserSigninLogs::count();
            $this->returnData['data'] = UserSigninLogs::with(['user' => function ($query) {
                return $query->field('id, username');
            }])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        return $this->view->fetch();
    }
}
