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

    protected function initialize()
    {
        parent::initialize();

        $this->model = new \app\admin\model\SystemConfig();

        $this->view->assign([
            'groupItemList' => $this->model->getGroupItemList(),
            'groupList' => $this->model->getGroupList(),
        ]);
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function getGroupList()
    {
        return json($this->getGroupList());
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

}
