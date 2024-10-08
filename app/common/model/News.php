<?php


namespace app\common\model;

use app\admin\model\Admin;

class News extends CommonModel
{

    public function getStatusTextAttr($value, $data)
    {
        return $this->getStatusList()[$data['status']];
    }

    protected function getStatusList()
    {
        return ['-1' => '已删除', '未发布',  '已发布'];
    }

    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getIsTopTextAttr($value, $data)
    {
        return $data['is_top'] === 1 ? '是' : '否';
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
