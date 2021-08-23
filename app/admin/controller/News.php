<?php


namespace app\admin\controller;


class News extends \app\common\controller\AdminController
{

    public function index()
    {
        return $this->view->fetch();
    }

    public function add()
    {
        return $this->view->fetch();
    }

    public function edit()
    {
        return $this->view->fetch();
    }

    public function delete()
    {
        return $this->view->fetch();
    }

    public function upload()
    {
        $saveName = (new \app\common\library\Attachment())->upload('file0');

        if (!$saveName) {
            $this->returnData['msg'] = '上传失败: 未找到文件';
            return $this->returnData;
        }

        $this->returnData['code'] = 1;
        $this->returnData['data'] = $saveName;
        $this->returnData['msg'] = '上传成功';

        return $this->returnData;
    }
}
