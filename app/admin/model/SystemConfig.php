<?php
/**
 * copyright@Administrator
 * 2023/11/18 0018 15:01
 * email:arron1118@icloud.com
 */

namespace app\admin\model;

class SystemConfig extends \app\common\model\CommonModel
{
    protected $json = [
        'content',
    ];

    protected $jsonAssoc = true;

    public function getGroupItemList()
    {
        return [
            'site' => [
                'title' => '基础设置',  // 组名
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
            'sms' => [
                'title' => '短信设置',
                'switch' => 0,  // 开关
            ],
        ];
    }

    public function getTypeList()
    {
        return [
            'text' => '文本',
            'textarea' => '文本域',
            'radio' => '单选项',
            'checkbox' => '多选项',
            'image' => '图片',
            'select' => '下拉框',
            'editor' => '编辑器',
        ];
    }

    public function getStatusList()
    {
        return [
            '禁用',
            '启用',
        ];
    }

    public function getRequiredList()
    {
        return [
            '选填',
            '必填',
        ];
    }

    public function getGroupList()
    {
        $list = $this->select()
            ->append(['type_text', 'status_text', 'required_text'])
            ->toArray();
        return $this->getGroupDataList($list);
    }

    protected function getGroupDataList($data)
    {
        $list = [];
        foreach ($data as $val) {
            $list[$val['group_name']][] = $val;
        }

        return $list;
    }

    public function getTypeTextAttr($value, $data)
    {
        return $this->getTypeList()[$data['type']];
    }

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    public function getRequiredTextAttr($value, $data)
    {
        return $this->getRequiredList()[$data['required']];
    }
}
