<?php


namespace app\common\model;

use \think\Model;
use app\admin\model\Admin;

class News extends Model
{

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return ['-1' => '已删除', '未发布',  '已发布'];
    }

    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getIsTopAttr($value)
    {
        return $value === 1 ? '是' : '否';
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
}
