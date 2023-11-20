<?php
/**
 * copyright@Administrator
 * 2023/11/15 0015 10:08
 * email:arron1118@icloud.com
 */

namespace app\common\traits;

use app\common\model\AnalysisRegion;

trait AjaxTrait
{


    /**
     * 获取菜单
     * @return mixed
     */
    public function getMenu()
    {
        $menu = config('menu.' . $this->module);

        $talent_on = true;
        if ($this->module === 'company') {
            $talent_on = $this->userInfo->talent_on;
        } elseif ($this->module === 'home') {
            $talent_on = $this->userInfo->company->talent_on;
        }

        if ($talent_on && $this->module !== 'admin') {
            foreach ($menu['menuInfo'][0]['child'] as $key => $val) {
                if (isset($val['name']) && $val['name'] === 'customer') {
                    isset($val['child'][1]) && $menu['menuInfo'][0]['child'][$key]['child'][2] = $val['child'][1];
                    $menu['menuInfo'][0]['child'][$key]['child'][1] = [
                        "name" => "talent_list",
                        "title" => "人才列表",
                        "href" => (string) url('/customer/talent'),
                        "target" => "_self",
                    ];
                }
            }
        }

        if ($this->module === 'admin' && $this->userInfo->id === 1) {
            $menu['menuInfo'][0]['child'][] = [
                "title" => "管理员",
                "href" => (string) url('/admin/index'),
                "icon" => "fa fa-user",
                "target" => "_self"
            ];
        }

        return json($menu);
    }

    public function getRegionList($pid = 0)
    {
        $list = AnalysisRegion::field('id, parent_id, region_name')->where(['parent_id' => $pid])->select();
        return json($list);
    }
}
