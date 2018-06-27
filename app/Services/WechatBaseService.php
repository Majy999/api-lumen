<?php

namespace App\Services;

use Symfony\Component\Cache\Simple\RedisCache;

class WechatBaseService
{
    /**
     * 获取微信配置信息
     * @param string $weixin
     * @return mixed
     */
    public function getWeChatOptions($weixin = 'corp-jike')
    {
        $options = [
            'corp_id' => 'ww8254a365bf92e5aa',
            'secret' => 'tkXAmtbl1bfMlFTeV3WQDgBQ_fauKOUt9SskWB5wrHk',
        ];

        $options = (object)$options;
        return $options;
    }

    /**
     * 微信配置缓存位置
     * @return RedisCache
     */
    public function wechatCache()
    {
        $predis = app('redis')->connection('default')->client();
        $cacheDriver = new RedisCache($predis);
        return $cacheDriver;
    }
}
