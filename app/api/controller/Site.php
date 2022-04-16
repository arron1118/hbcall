<?php

namespace app\api\controller;

class Site extends \app\common\controller\ApiController
{
    protected $noNeedLogin = ['getSiteInfo'];

    public function getSiteInfo()
    {
        $this->returnData['msg'] = 'success';
        $this->returnData['data'] = [
            'logo' => $this->request->domain() . '/static/images/app-logo.png',
            'corporation' => '东莞汇邦软件科技有限公司',
            'intro' => '东莞汇邦软件科技有限公司位于东莞市南城区鸿福西路南城商务大厦，是一家专注于人力资源管理软件研究、开发及解决方案的高新技术企业.，以CRM系统、OA办公系统、ERP企业管理系统博得用户广泛的好评。致力于将企业同客户互动的全过程数字化、智能化， 帮助企业提升客户满意度，实现可持续的业绩增长!',
            'wechat' => $this->request->domain() . '/static/images/wechat-contact.png',
        ];
        $this->returnData['code'] = 1;

        return json($this->returnData);
    }
}
