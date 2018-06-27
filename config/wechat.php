<?php
return [

    /**
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug' => false,

    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id' => 'xxxx',         // AppID
    'secret' => 'xxxx',     // AppSecret
    'token' => 'your-token',          // Token
    'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！

    /**
     * Guzzle 全局设置
     *
     * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
     */
    'guzzle' => [
        'timeout' => 3.0, // 超时时间（秒）
        'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
    ],
];