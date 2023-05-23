<?php
declare (strict_types = 1);

namespace app;

use Jenssegers\Agent\Agent;
use think\App;
use think\exception\ValidateException;
use think\facade\View;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * View 实例
     * @var null
     */
    protected $view = null;

    /**
     * Agent 实例
     * @var Agent
     */
    protected $agent;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 业务模块
     * @var string
     */
    protected $module = '';

    /**
     * Token 过期时间
     * @var float|int
     */
    protected $token_expire_time = 3600 * 24 * 7;

    /**
     * baseFile
     * @var string
     */
    protected $baseFile = '';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->view = View::instance();
        $this->agent = new Agent();
        $this->baseFile = $this->request->baseFile();
        $this->module = app('http')->getName();

        $this->view->assign([
            'module' => $this->module,
            'app_name' => config('app.app_name'),
            'baseFile' => $this->baseFile,
        ]);

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 获取菜单
     * @return mixed
     */
    public function getMenu()
    {
        $menu = config('menu.' . $this->module);

        $talent_on = true;
        if ($this->module === 'company') {
            $talent_on = $this->userInfo->talent_on;
        } elseif ($this->module === 'home') {
            $talent_on = $this->userInfo->company->talent_on;
        }

        if ($talent_on && $this->module !== 'admin') {
            foreach ($menu['menuInfo'][0]['child'] as $key => $val) {
                if (isset($val['name']) && $val['name'] === 'customer') {
                    isset($val['child'][1]) && $menu['menuInfo'][0]['child'][$key]['child'][2] = $val['child'][1];
                    $menu['menuInfo'][0]['child'][$key]['child'][1] = [
                        "name" => "talent_list",
                        "title" => "人才列表",
                        "href" => (string) url('/customer/talent'),
                        "target" => "_self",
                    ];
                }
            }
        }

        if ($this->module === 'admin' && $this->userInfo->id === 1) {
            $menu['menuInfo'][0]['child'][] = [
                "title" => "管理员",
                "href" => (string) url('/admin/index'),
                "icon" => "fa fa-user",
                "target" => "_self"
            ];
        }

        return json($menu);
    }
}
