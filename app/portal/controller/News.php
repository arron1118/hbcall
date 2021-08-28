<?php


namespace app\portal\controller;

use app\common\facade\News as NewsLibrary;

class News extends \app\common\controller\PortalController
{

    public function index()
    {
        $this->view->assign(NewsLibrary::getList());
        return $this->view->fetch();
    }

    public function detail($id)
    {
        $news = NewsLibrary::getDetail($id);

        $this->view->assign([
            'news' => $news,
            'title' => $news->title,
            'keywords' => $news->keyword ?: $news->content,
            'description' => $news->intro ?: $news->content
        ]);
        return $this->view->fetch();
    }
}
