<?php


namespace app\portal\controller;


class Index extends \app\common\controller\PortalController
{
    public function index()
    {
        $this->view->assign([
            'title' => '客户服务电话外呼系统,不封号的呼叫中心',
            'keywords' => '客户服务呼叫中心,呼叫中心服务,电话呼叫中心,外呼系统,呼叫中心,云呼叫中心',
            'description' => '喵头鹰呼叫中心为企业提供完整客服外呼系统解决方案,电话外呼系统、打造云呼叫中心客服系统及电话营销系统一体化解决方案,特批白名单,不封号，联系电话：13622850769'
        ]);

        return $this->view->fetch();
    }

    public function product()
    {
        $this->view->assign('title', '产品');
        return $this->view->fetch();
    }

    public function cooperate()
    {
        $this->view->assign('title', '伙伴');
        return $this->view->fetch();
    }

    public function solution()
    {
        $this->view->assign('title', '方案');
        return $this->view->fetch();
    }

    public function case()
    {
        $this->view->assign('title', '案例');
        return $this->view->fetch();
    }

    public function about()
    {
        $this->view->assign('title', '关于');
        return $this->view->fetch();
    }

    public function buy()
    {
        $this->view->assign([
            'title' => '外呼系统多少钱_电销客户系统多少钱',
            'keywords' => '外呼系统多少钱_电销客户系统多少钱',
            'description' => '特批白名单不封号,免费试用,喵头鹰呼叫中心为企业提供完整客服外呼系统购买方案,给客户提供一套适合他们公司的外呼系统搭建费用清单,联系电话：13622850769'
        ]);
        return $this->view->fetch();
    }

    public function hello()
    {
        return 'Test';
    }
}
