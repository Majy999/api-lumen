<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// 测试路由
$router->group(['prefix' => 'test'], function () use ($router) {
    $router->get('test', function () {
        return 'test';
    });

    $router->get('mysql-get', ['as' => 'mysql-get', 'uses' => 'TestController@mysqlGet']);
    $router->get('mysql-test', ['as' => 'mysql-test', 'uses' => 'TestController@mysql']);
    $router->get('redis-test', ['as' => 'redis-test', 'uses' => 'TestController@redis']);
});

// 回调地址
$router->group(['prefix' => 'receive'], function () use ($router) {

    // 数据回调URL
    $router->get('data-receive', ['as' => 'data-receive', 'uses' => 'Work\ReceiveController@dataReceive']);
    $router->post('data-receive', ['as' => 'data-receive', 'uses' => 'Work\ReceiveController@dataReceive']);

    // 数据回调URL
    $router->get('work-register-receive', ['as' => 'work-register-receive', 'uses' => 'Work\WorkRegisterReceiveController@workRegisterReceive']);
    $router->post('work-register-receive', ['as' => 'work-register-receive', 'uses' => 'Work\WorkRegisterReceiveController@workRegisterReceive']);
});

// 第三方应用授权
$router->get('work-server', ['as' => 'work-server-authorization', 'uses' => 'WorkServerController@workServerAuthorization']);

// 创建员工
$router->get('create-user', ['as' => 'create-user', 'uses' => 'WorkServerController@createUser']);

// 创建员工
$router->get('user-list', ['as' => 'user-list', 'uses' => 'WorkServerController@userList']);

// 消息通知
$router->group(['prefix' => 'message'], function () use ($router) {
    // 设置授权配置 测试
    $router->get('get-permanent-code', ['as' => 'get-permanent-code', 'uses' => 'Work\MessageController@getPermanentCode']);
});

// 加群页面
$router->get('join-group', ['as' => 'join-group', 'uses' => 'WebController@joinGroupView']);

// 设置微信
$router->get('wx-setting', ['as' => 'wx-setting', 'uses' => 'WebController@wxSettingView']);

// 微信模拟登录
$router->get('login-wxmock', ['as' => 'login-wxmock', 'uses' => 'WebController@loginWxmock']);