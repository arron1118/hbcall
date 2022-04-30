<?php

use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::miss(function () {
    return '404 Not Found!';
});

Route::get('hello/:name', 'index/hello');

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

