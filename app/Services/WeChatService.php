<?php

namespace App\Services;

use EasyWeChat\Factory;

class WeChatService extends WechatBaseService
{
    protected $options;
    protected $workProgram;

    public function __construct($weixin = 'wotk-zantui')
    {
        $cacheDriver = $this->wechatCache();
        $option = $this->getWeChatOptions($weixin);

        $this->options = [
            'corp_id' => $option->corp_id,
            'agent_id' => $option->agent_id,
            'secret' => $option->secret,
            'guzzle' => [
                'verify' => false,
            ],

            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
        ];

        $this->workProgram = Factory::work($this->options);
        $this->workProgram['cache'] = $cacheDriver;
    }

    /**
     * 获取用户的openid
     *
     * @param       string $code code
     *
     * @return      string         openid               用户openid
     */
    public function getOpenid($code)
    {
        $code = str_replace(' ', '+', $code);
        $info = $this->workProgram->auth->session($code);
        return $info['openid'];
    }

    /**
     * 获取小程序 access_token
     */
    public function getAccessToken()
    {
        $accessToken = $this->workProgram->access_token->getToken();
        return $accessToken;
    }

    /**
     * 获取企业号应用详情
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date 2018/6/27 18:20
     * @param $agentId
     * @return mixed
     */
    public function getAgentDetail($agentId)
    {
        $agentDetail = $this->workProgram->agent->get($agentId); // 只能传配置文件中的 id，API 改动所致
        return $agentDetail;
    }

    /**
     * callback
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date xxx
     * @since PM_mock_data
     * @return mixed|void
     */
    public function callback()
    {

    }
}