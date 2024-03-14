<?php
/**
 * copyright@Administrator
 * 2024/3/7 0007 17:49
 * email:arron1118@icloud.com
 */

namespace app\common\traits;

use app\common\model\Company;
use app\common\model\CompanySigninLogs;
use app\common\model\UserSigninLogs;

trait SigninLogsTrait
{

    public function companyLogs()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $companyId = $this->module === 'company' ? $this->userInfo->id : $this->request->param('company_id/d', 0);
            $map = [];

            if ($companyId > 0) {
                $map[] = ['company_id', '=', $companyId];
            }

            $this->returnData['count'] = CompanySigninLogs::where($map)->count();
            $this->returnData['data'] = CompanySigninLogs::where($map)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        $this->init();

        return $this->view->fetch('common@signin_logs/company_logs');
    }

    public function userLogs()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $companyId = $this->module === 'company' ? $this->userInfo->id : $this->request->param('company_id/d', 0);
            $userId = $this->module === 'home' ? $this->userInfo->id : $this->request->param('user_id/d', 0);
            $map = [];

            if ($companyId > 0) {
                $map[] = ['company_id', '=', $companyId];
            }

            if ($userId > 0) {
                $map[] = ['user_id', '=', $userId];
            }
            $this->returnData['count'] = UserSigninLogs::where($map)->count();
            $this->returnData['data'] = UserSigninLogs::where($map)
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        $this->init();

        return $this->view->fetch('common@signin_logs/user_logs');
    }

    protected function init()
    {
        if ($this->module === 'admin') {
            $company = (new Company())->getCompanyList();
            $this->view->assign('company', $company->toArray());
        }

        if ($this->module === 'company') {
            $this->view->assign('users', $this->userInfo->user);
        }
    }

}
