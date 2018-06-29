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
            'agent_id' => 1000014,
            'secret' => 'vPpXjQWZnIjXR4jp7QH3_s-2rKcXMjVtOT91k325MXs',
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
