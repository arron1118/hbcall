<?php
/**
 * copyright@Administrator
 * 2023/10/17 0017 16:38
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

use app\common\model\CallTypeLogs as CallTypeLogsModel;

class CallTypeLogs extends \app\common\controller\AdminController
{

    public function index()
    {
        if ($this->request->isPost()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);

            $this->returnData['count'] = CallTypeLogsModel::count();
            $this->returnData['data'] = CallTypeLogsModel::with(['admin' => function ($query) {
                return $query->field('id, username');
            }, 'company' => function ($query) {
                return $query->field('id, username');
            }, 'callType' => function ($query) {
                return $query->field('id, title');
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
