<?php


namespace app\common\library;

use app\common\model\News as NewsModel;

class News
{
    protected $map = [
        ['status', '=', 1]
    ];

    public function getList($page = 1, $limit = 10)
    {
        $count = NewsModel::where($this->map)->count();

        $list = NewsModel::with('author')
            ->where($this->map)
            ->order('update_time DESC')
            ->limit(($page - 1) * $limit, $limit)
            ->select()->toArray();

        return ['count' => $count, 'list' => $list, 'top' => $list[0]];
    }

    public function updateView($id)
    {
        return NewsModel::where('id', '=', $id)->inc('view')->update();
    }

    public function getDetail($id)
    {
        if (!$id) {
            return false;
        }

        return NewsModel::with('author')->where($this->map)->find($id);
    }
}
