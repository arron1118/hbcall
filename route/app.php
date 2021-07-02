<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::miss(function () {
    return '404 Not Found!';
});

Route::get('hello/:name', 'index/hello');

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
/*
Route::get('test', function () {
    return 'api/user/index';
});*/

//Route::get('test', '/api/user/index');
