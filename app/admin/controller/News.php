<?php


namespace app\admin\controller;


use think\facade\Config;
use think\facade\Session;

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
            $param['is_top'] = $param['is_top'] ? 1 : 0;
            $param['update_time'] = time();
            $news->save($param);

            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '发布成功';
            $this->returnData['data'] = $news->id;

            return json($this->returnData);
        }

        $this->view->assign('news', $news);
        return $this->view->fetch();
    }

    public function delete($id = 0)
    {
        if (!$id) {
            $this->returnData['msg'] = '未指定ID';
            return json($this->returnData);
        }

        $res = $this->model::update(['id' => $id, 'status' => '-1']);

        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '删除成功';

        return json($this->returnData);
    }

    public function upload()
    {
        $upload = (new \app\common\library\Attachment())->upload('file');

        if (!$upload) {
            $this->returnData['msg'] = '上传失败: 未找到文件';
            return $this->returnData;
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->returnData['msg'] = '上传成功';

        return $this->returnData;
    }
}
