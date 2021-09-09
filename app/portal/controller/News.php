<?php


namespace app\portal\controller;

use app\common\facade\News as NewsLibrary;

class News extends \app\common\controller\PortalController
{

    public function index()
    {
        $this->view->assign([
            'title' => '呼叫中心系统方案_电销系统呼叫搭建方案',
            'keywords' => '客户服务呼叫中心,呼叫中心服务,电话呼叫中心,呼叫中心系统,呼叫中心,云呼叫中心',
            'description' => '呼叫中心方案就是根据客户的基本需要以及结合企业的产品功能，给客户做出一整套适合他们公司的呼叫中心系统，包括呼叫中心定义，呼叫系统流程，呼叫中心的功能，客户咨询问题等等。'
        ]);
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

    public function getList()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 10);

        $list = NewsLibrary::getList($page, $limit);
        return json($list);
    }
}
