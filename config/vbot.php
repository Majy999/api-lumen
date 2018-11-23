<?php

$path = public_path() . '/tmp/';

return [
    'path'     => $path,
    /*
     * swoole 配置项（执行主动发消息命令必须要开启）
     */
    'swoole'  => [
        'status' => true,
        'ip'     => '127.0.0.1',
        'port'   => '8866',
    ],
    /*
     * 下载配置项
     */
    'download' => [
        'image'         => false,
        'voice'         => false,
        'video'         => false,
        'emoticon'      => false,
        'file'          => false,
        'emoticon_path' => $path.'emoticons',
    ],
    /*
     * 输出配置项
     */
    'console' => [
        'output'  => true, // 是否输出
        'message' => false, // 是否输出接收消息 （若上面为 false 此处无效）
    ],
    /*
     * 日志配置项
     */
    'log'      => [
        'level'         => 'debug',
        'permission'    => 0777,
        'system'        => $path.'log',
        'message'       => $path.'log',
    ],
    /*
     * 缓存配置项
     */
    'cache' => [
        'default' => 'redis',
        'stores'  => [
            'file' => [
                'driver' => 'file',
                'path'   => $path.'cache',
            ],
            'redis' => [
                'driver'     => 'redis',
                'connection' => 'default',
            ],
        ],
    ],
    'database' => [
        'redis' => [
            'client'  => 'predis',
            'default' => [
                'host'     => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', 123456),
                'port'     => env('REDIS_PORT', 6379),
                'database' => env('REDIS_DATABASE', 0),
            ],
        ],
    ],
];
