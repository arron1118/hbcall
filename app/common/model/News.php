<?php


namespace app\common\model;


class News extends \think\Model
{

    public function getStatusAttr($value)
    {
        return $this->getStatusList()[$value];
    }

    protected function getStatusList()
    {
        return ['-1' => '已删除', '0' => '未发布', '1' => '已发布'];
    }

    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getIsTopAttr($value)
    {
        return $value === 1 ? '是' : '否';
    }

    public function getCreateTimeAttr($value)
    {
        return date($this->dateFormat, $value);
    }

    public function getUpdateTimeAttr($value)
    {
        return date($this->dateFormat, $value);
    }

    public function author()
    {
        return $this->belongsTo('app\admin\model\Admin', 'author_id');
    }
}
