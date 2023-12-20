<?php
/**
 * copyright@Administrator
 * 2023/11/18 0018 15:03
 * email:arron1118@icloud.com
 */

namespace app\admin\controller;

use think\facade\Event;

class SystemConfig extends \app\common\controller\AdminController
{
    protected $groupList = [];

    protected function initialize()
    {
        parent::initialize();

        $this->model = new \app\admin\model\SystemConfig();
        $this->groupList = $this->model->getGroupList();

        $this->view->assign([
            'groupItemList' => $this->model->getGroupItemList(),
            'groupList' => $this->groupList,
            'typeList' => $this->model->getTypeList(),
        ]);
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function getGroupList()
    {
        $this->returnData['msg'] = lang('Operation successful');
        $this->returnData['data'] = $this->groupList;
        return json($this->returnData);
    }

    /**
     * 保存配置
     * @return \think\response\Json
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $this->returnData['msg'] = lang('Saved successfully');
            try {
                foreach ($post as $key => $val) {
                    $this->model
                        ->where('name', $key)
                        ->update([
                            'value' => $val,
                        ]);
                }

                Event::trigger('UpdateSystemConfig');
            } catch (\Exception $e) {
                $this->returnData['msg'] = lang('Save failed');
            }
        }

        return json($this->returnData);
    }

    public function edit($id)
    {
        $info = $this->model->find($id);
        if (!$info) {
            $this->returnData['msg'] = '参数错误';
            return json($this->returnData);
        }

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = lang('Operation successful');
        if ($this->request->isPost()) {

        }

        $this->returnData['data'] = $info;
        return json($this->returnData);
    }

    public function del($id)
    {
        if ($this->request->isPost()) {
            if ($this->model->destroy(function ($query) use ($id) {
                $query->where('id', '=', $id);
            })) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            }
        }

        return json($this->returnData);
    }
}
