<?php

namespace app\admin\controller;

use app\common\model\NumberStore;
use app\common\model\User;
use app\company\model\Company;

class XnumberStore extends \app\common\controller\AdminController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
    }

    public function index()
    {
        // 小号对应企业
//        $number = NumberStore::find(1);
//        foreach ($number->companyXnumber as $key => $val) {
//            dump($val->company->toArray());
//        }
        // 小号对应用户
//        $number = NumberStore::find(1);
//        foreach ($number->userXnumber as $key => $val) {
//            dump($val->user->hidden(['password'])->toArray());
//        }

        // 企业小号
//        $company = Company::find(2);
//        $companyXnumber = $company->companyXnumber;
//        dump($companyXnumber->number_store_id);
//        dump($companyXnumber->numberStore->number);

        // 用户小号
//        $user = User::find(71);
//        $userXnumber = $user->userXnumber;
//        dump($userXnumber);
//        dump($userXnumber->numberStore->number);
//        $userXnumber->number_store_id = 2;
//        $userXnumber->save();

        return $this->view->fetch();
    }

    public function getNumberList()
    {
        if ($this->request->isAjax()) {
            $page = (int) $this->request->param('page', 1);
            $limit = (int) $this->request->param('limit', 10);
            $total = NumberStore::count();
            $numberList = NumberStore::withCount(['userXnumber', 'companyXnumber'])
                ->order('id', 'desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            return json(['rows' => $numberList, 'total' => $total, 'msg' => '操作成功', 'code' => 1]);
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            if ((new NumberStore())->save($this->request->param())) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '添加成功';
            } else {
                $this->returnData['msg'] = '添加失败';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            $id = (int) $this->request->param('id');
            $number = trim($this->request->param('number'));
            $num = NumberStore::find($id);
            $num->number = $number;
            if ($num->save()) {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '修改成功';
            } else {
                $this->returnData['msg'] = '修改失败';
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }
}