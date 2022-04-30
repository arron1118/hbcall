<?php

namespace app\admin\controller;

use think\db\exception\DbException;

class News extends \app\common\controller\AdminController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = \app\common\model\News::class;
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $map = [
                ['status', '<>', '-1']
            ];
            $page = $this->request->param('page', 1);
            $limit = $this->request->param('limit', 10);
            $search_title = $this->request->param('title', '');
            $search_time = $this->request->param('time', '');

            if ($search_title) {
                $map[] = ['title', 'like', '%' . $search_title . '%'];
            }

            if ($search_time) {
                $daytime = strtotime($search_time);
                $map[] = ['update_time', 'between', [$daytime, $daytime + 86400 - 1]];
            }

            $count = $this->model::where($map)->count();

            $newsList = $this->model::where($map)->limit(($page - 1) * $limit, $limit)->order('update_time DESC')->select();

            $this->returnData['code'] = 1;
            $this->returnData['data'] = $newsList;
            $this->returnData['msg'] = 'Success';
            $this->returnData['count'] = $count;

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $title = trim($this->request->param('title'));
            $content = $this->request->param('content');
            $cover_img = $this->request->param('cover_img');
            $is_top = $this->request->param('is_top', 0);

            if ($title === '') {
                $this->returnData['msg'] = '请输入标题';
                return json($this->returnData);
            }

            if ($this->model::where('title', '=', $title)->find()) {
                $this->returnData['msg'] = '标题已经存在';
                return json($this->returnData);
            }

            $news = new $this->model();
            $news->title = $title;
            $news->content = $content;
            $news->cover_img = $cover_img;
            $news->is_top = $is_top;
            $news->status = 1;
            $news->create_time = time();
            $news->update_time = time();
            $news->author_id = $this->userInfo['id'];
            $news->save();

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '发布成功';
            $this->returnData['data'] = $news->id;

            return json($this->returnData);
        }


        return $this->view->fetch();
    }

    public function edit($id = 0)
    {
        if (!$id) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $news = $this->model::find($id);

        if (!$news) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['is_top'] = isset($param['is_top']) ?: 0;
            $param['update_time'] = time();
            $news->save($param);

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '更新成功';
            $this->returnData['data'] = $news->id;

            return json($this->returnData);
        }

        $this->view->assign('news', $news);
        return $this->view->fetch();
    }

    public function delete($ids)
    {
        if (!$ids) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $res = $this->model::where('id', 'in', $ids)->delete();

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '删除成功';
        $this->returnData['data'] = $res;

        return json($this->returnData);
    }

    public function import()
    {
        $data = $this->request->param('data');
        if (!$data) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        $list = [];
        foreach($data as $key => &$val) {
            $val['create_time'] = time();
            $val['update_time'] = time();
            $val['status'] = 1;
            $val['author_id'] = $this->userInfo['id'];

            $list[$key] = $val;
        }

        try {
            $res = (new $this->model)->saveAll($data);

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '导入成功';
            $this->returnData['data'] = $res;

            return json($this->returnData);
        } catch (DbException $dbException) {
            $this->returnData['code'] = $dbException->getCode();
            $this->returnData['msg'] = $dbException->getMessage();
            $this->returnData['data'] = $dbException->getData();

            return json($this->returnData);
        }
    }
}
