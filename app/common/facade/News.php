<?php


namespace app\common\facade;


class News extends \think\Facade
{

    protected static function getFacadeClass()
    {
        return 'app\common\library\News';
    }
}
