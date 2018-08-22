<?php

$router->get('customer-server', ['as' => 'customer-server-get', 'uses' => 'CustomerServerController@customerServer']);
$router->post('customer-server', ['as' => 'customer-server-post', 'uses' => 'CustomerServerController@customerServer']);