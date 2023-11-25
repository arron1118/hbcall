<?php
/**
 * copyright@Administrator
 * 2023/11/25 0025 16:36
 * email:arron1118@icloud.com
 */

namespace app\common\subscribe;

use think\facade\Cache;

class Config
{

    public function onUpdateSystemConfig()
    {
        Cache::tag('system_config')->clear();
    }

    public function onUpdateCompanyConfig()
    {
        Cache::tag('company_config')->clear();
    }

    public function onUpdateUserConfig()
    {
        Cache::tag('user_config')->clear();
    }
}
