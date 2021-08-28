<?php

use think\facade\Route;

Route::get('index', 'index/index');
Route::get('product', 'index/product');
Route::get('cooperate', 'index/cooperate');
Route::get('solution', 'index/solution');
Route::get('case', 'index/case');
Route::get('about', 'index/about');
Route::get('buy', 'index/buy');
Route::get('news$', 'news/index');
Route::get('news/:id', 'news/detail');


