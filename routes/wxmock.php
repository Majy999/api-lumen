<?php

$router->get('get-qrcode', ['as' => 'get-qrcode', 'uses' => 'LoginController@getQrcode']);

// 提交图片
$router->post('upload-qrcode', ['as' => 'upload-qrcode', 'uses' => 'LoginController@uploadQrcode']);

$router->get('send', ['as' => 'send', 'uses' => 'LoginController@send']);
$router->get('search', ['as' => 'send', 'uses' => 'LoginController@search']);