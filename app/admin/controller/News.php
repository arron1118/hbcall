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
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $search_title = $this->request->param('title', '');
            $search_time = $this->request->param('time', '');

            $map = [
                ['status', '<>', '-1']
            ];
            if ($search_title) {
                $map[] = ['title', 'like', '%' . $search_title . '%'];
            }

            if ($search_time) {
                $daytime = strtotime($search_time);
                $map[] = ['update_time', 'between', [$daytime, $daytime + 86400 - 1]];
            }

            $this->returnData['count'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::where($map)
                ->limit(($page - 1) * $limit, $limit)
                ->order('id DESC')
                ->append(['status_text', 'is_top_text'])
                ->select();
            $this->returnData['msg'] = lang('Operation successful');

            return json($this->returnData);
        }

        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['status'] = 1;
            $params['author_id'] = $this->userInfo->id;

            if ($params['title'] === '') {
                $this->returnData['msg'] = '请输入标题';
                return json($this->returnData);
            }

            if ($this->model::where('title', '=', $params['title'])->find()) {
                $this->returnData['msg'] = '标题已经存在';
                return json($this->returnData);
            }

            $news = new $this->model();
            if ($news->save($params)) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '发布成功';
                $this->returnData['data'] = $news->id;
            }
        }

        return json($this->returnData);
    }

    public function edit($id = 0)
    {
        if (!$id) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $news = $this->model::find($id);
        if (!$news) {
            $this->returnData['msg'] = lang('No data was found');
            return json($this->returnData);
        }

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = lang('Operation successful');

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $param['is_top'] = isset($param['is_top']) ?: 0;

            if ($param['title'] === '') {
                $this->returnData['msg'] = '请输入标题';
                $this->returnData['code'] = 0;
                return json($this->returnData);
            }

            if ($this->model::where('title', '=', $param['title'])->find()) {
                $this->returnData['msg'] = '标题已经存在';
                $this->returnData['code'] = 0;
                return json($this->returnData);
            }

            if ($news->save($param)) {
                $this->returnData['msg'] = '更新成功';
            }
        }

        $this->returnData['data'] = $news;
        return json($this->returnData);
    }

    public function delete($ids)
    {
        if (!$ids) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $res = $this->model::destroy(function ($query) use ($ids) {
            $query->where('id', 'in', $ids);
        });

        if ($res) {
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除成功';
            $this->returnData['data'] = $res;
        }

        return json($this->returnData);
    }

    public function import()
    {
        $data = $this->request->param('data');
        if (!$data) {
            $this->returnData['msg'] = '未找到相关数据';
            return json($this->returnData);
        }

        foreach ($data as &$val) {
            $val['create_time'] = time();
            $val['update_time'] = time();
            $val['status'] = 1;
            $val['author_id'] = $this->userInfo['id'];
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
