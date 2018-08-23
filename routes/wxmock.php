<?php

$router->get('get-qrcode', ['as' => 'get-qrcode', 'uses' => 'LoginController@getQrcode']);

// 提交图片
$router->post('upload-qrcode', ['as' => 'upload-qrcode', 'uses' => 'LoginController@uploadQrcode']);
