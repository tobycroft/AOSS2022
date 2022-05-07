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


Route::any(':version/:module/:controller/:function', '\app\:version\:module\controller\:controller@:function')->allowCrossDomain([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Credentials' => 'true',
    "Access-Control-Allow-Methods" => ["GET", "POST", "PATCH", "PUT", "DELETE"],
]);


Route::any('up', '\app\v1\file\controller\index@up')->allowCrossDomain([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Credentials' => 'true',
]);
Route::any('upfull', '\app\v1\file\controller\index@upfull')->allowCrossDomain([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Credentials' => 'true',
]);

Route::any(':any', function () {
    return \think\facade\Request::url();
})->allowCrossDomain([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Credentials' => 'true',
]);

Route::any('', function () {
    return \think\facade\Request::url();
})->allowCrossDomain([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Credentials' => 'true',
]);
