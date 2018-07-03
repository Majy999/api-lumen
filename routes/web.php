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

    // 指令回调URL
    $router->get('handle-receive', ['as' => 'handle-receive', 'uses' => 'Work\ReceiveController@handleReceive']);
    $router->post('handle-receive', ['as' => 'handle-receive', 'uses' => 'Work\ReceiveController@handleReceive']);

    // 获取第三方应用凭证 suite_access_token
    $router->get('get-suite-access-token', ['as' => 'get-suite-access-token', 'uses' => 'Work\ReceiveController@getSuiteAccessToken']);

    // 获取预授权码 pre_auth_code
    $router->get('get-pre-auth-code', ['as' => 'get-pre-auth-code', 'uses' => 'Work\ReceiveController@getPreAuthCode']);

    // 设置授权配置 测试
    $router->get('make-test', ['as' => 'make-test', 'uses' => 'Work\ReceiveController@makeTest']);
});