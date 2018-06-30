<?php

namespace App\Http\Controllers\Work;

use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\WeChatService;
use Extend\WorkWechat\Server\WXBizMsgCrypt;

class ReceiveController extends Controller
{
    // 数据回调URL
    public function dataReceive()
    {
        // 假设企业号在公众平台上设置的参数如下
        $encodingAesKey = "vwvYPSPikSxymLof4Ri7RAzVfchzZHv7VTgkifcV18k";
        $token = "FRLiucjHsmi8t9";
        $corpId = "ww8254a365bf92e5aa";

        /*
        ------------使用示例一：验证回调URL---------------
        *企业开启回调模式时，企业号会向验证url发送一个get请求
        假设点击验证时，企业收到类似请求：
        * GET /cgi-bin/wxpush?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780&echostr=P9nAzCzyDtyTWESHep1vC5X9xho%2FqYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp%2B4RPcs8TgAE7OaBO%2BFZXvnaqQ%3D%3D
        * HTTP/1.1 Host: qy.weixin.qq.com

        接收到该请求时，企业应
        1.解析出Get请求的参数，包括消息体签名(msg_signature)，时间戳(timestamp)，随机数字串(nonce)以及公众平台推送过来的随机加密字符串(echostr),
        这一步注意作URL解码。
        2.验证消息体签名的正确性
        3. 解密出echostr原文，将原文当作Get请求的response，返回给公众平台
        第2，3步可以用公众平台提供的库函数VerifyURL来实现。

        */

         $sVerifyMsgSig = request("msg_signature");
         $sVerifyTimeStamp = request("timestamp");
         $sVerifyNonce = request("nonce");
         $sVerifyEchoStr = request("echostr");

        // 需要返回的明文
        $sEchoStr = "";

        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
        if ($errCode == 0) {
            echo($sEchoStr);
        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }

    // 指令回调URL
    public function handleReceive()
    {
        $input = file_get_contents('php://input');
        Tools::logInfo('指令回调URL');
        Tools::logInfo($input);
        return true;
    }

    public function getAccessToken()
    {
        $weChatService = new WeChatService();
        $accessToken = $weChatService->getAccessToken();
        echo $accessToken;
    }

}
