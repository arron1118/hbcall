<?php
/**
 * copyright@Administrator
 * 2023/11/18 0018 15:01
 * email:arron1118@icloud.com
 */

namespace app\admin\model;

class SystemConfig extends \app\common\model\CommonModel
{

    public function getGroupItemList()
    {
        return [
            'site' => [
                'title' => '基础设置',  // 组名
                'switch' => 1,  // 开关
            ],
            'sms' => [
                'title' => '短信设置',
                'switch' => 1,  // 开关
            ],
            'upload' => [
                'title' => '上传设置',
                'switch' => 1,  // 开关
            ],
            'wechat' => [
                'title' => '微信设置',
                'switch' => 1,  // 开关
            ],
        ];
    }

    public function getGroupList()
    {
        return $this->where(['status' => 1])->select();
    }

}
