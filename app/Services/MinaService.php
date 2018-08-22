<?php

namespace App\Services;

use EasyWeChat\Factory;
use Symfony\Component\Cache\Simple\RedisCache;

class MinaService
{
    protected $options;
    protected $miniProgram;
    protected $openPlatform;

    public function __construct($weixin = 'jike-wap')
    {
        $predis = app('redis')->connection('default')->client();
        $cacheDriver = new RedisCache($predis);

        $option = [
            'app_id' => 'wx2ff39cb77a670249',
            'app_secret' => '2a2d19a4d4e57c0f353c4266a3e5f676',
            'token' => 'token',
            'aes_key' => '',
        ];
        $this->options = [
            'app_id' => $option['app_id'],
            'secret' => $option['app_secret'],
            'token' => $option['token'],
            'aes_key' => $option['aes_key'],
            'guzzle' => [
                'verify' => false,
            ],
        ];

        $this->miniProgram = Factory::MiniProgram($this->options);
        $this->miniProgram['cache'] = $cacheDriver;
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
        $info = $this->miniProgram->auth->session($code);
        return $info['openid'];
    }

    /**
     * 获取小程序 access_token
     */
    public function getAccessToken()
    {
        $accessToken = $this->miniProgram->access_token->getToken();
        return $accessToken;
    }

    /**
     *
     * 小程序登录
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return array
     */
    public function minaLogin($code, $iv, $encryptedData)
    {
        $code = str_replace(' ', '+', $code);

        // 获取到用户的session_key
        $info = $this->miniProgram->auth->session($code);

        if (isset($info['session_key'])) {
            // 获取用户基本信息
            $iv = str_replace(' ', '+', $iv);
            $userInfo = $this->miniProgram->encryptor->decryptData($info['session_key'], $iv, $encryptedData);

            $response = [
                'openid' => $info['openid'],
                'userInfo' => $userInfo
            ];
            return $response;
        }
    }

    /**
     * 发送客服消息
     * @param $message
     * @param $openId
     * @return bool
     */
    public function customerServerSend($message, $openId)
    {
        $customerService = $this->miniProgram->customer_service;
        $result = $customerService->message($message)->to($openId)->send();
        return $result;
    }

    /**
     * 接收用户发送过来的消息，和用户触发的一系列事件
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|string
     */
    public function receiveMessages()
    {
        $message = $this->miniProgram->server->getMessage();
        return $message;
    }
}