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


Route::any(':version/:module/:controller/:function', '\app\:version\:module\controller\:controller@:function')->allowCrossDomain();


Route::any('up', '\app\v1\file\controller\index@up')->allowCrossDomain();
Route::any('upfull', '\app\v1\file\controller\index@upfull')->allowCrossDomain();

Route::any(':any', function () {
    return \think\facade\Request::url();
})->allowCrossDomain();

Route::any('', function () {
    return \think\facade\Request::url();
})->allowCrossDomain();
