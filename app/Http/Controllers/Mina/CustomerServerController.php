<?php

namespace App\Http\Controllers\Mina;

use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\MinaService;
use EasyWeChat\Kernel\Messages\Raw;

class CustomerServerController extends Controller
{
    /**
     * 客服回调接收地址
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/8/6 12:26
     * @since PM_1.1_ws
     */
    public function customerServer()
    {
        Tools::logInfo("其他请求" . PHP_EOL);
        // 第一次绑定消息推送url的时候，调用该方法
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->valid();
        } else {
            $minaService = new MinaService();

            // 接收用户发送过来的消息，和用户触发的一系列事件
            $receiveMessages = $minaService->receiveMessages();
            Tools::logInfo($receiveMessages, '接收用户发送过来的消息');

            $openId = $receiveMessages['FromUserName'] ?? '';
            $event = $receiveMessages['Event'] ?? '';
            $msgType = $receiveMessages['MsgType'] ?? '';
            $content = $receiveMessages['Content'] ?? '';
            $sessionFrom = $receiveMessages['SessionFrom'] ?? '';

            // 点击客服按钮进入客服
            if ($event == 'user_enter_tempsession') {
                $sessionFrom = explode(',', $sessionFrom);
            } else {
                if ($content == '我要开店') {
                    $title = '集客';
                    $logo = 'http://img.jkweixin.com/defaults/b-image/page/icon-login-logo@2x.png';
                    $host = 'https://h5.jkweixin.com/?type=ws#';
                    $url = $host . '/ws-qr-code?employee_id=100001';
                    $message = new Raw('{
                        "touser": "' . $openId . '",
                        "msgtype": "link",
                        "link": {
                              "title": "' . $title . ': 扫码关注企业微信",
                              "description": "长按扫码关注企业微信",
                              "url": "' . $url . '",
                              "thumb_url": "' . $logo . '"
                        }
                  }');
                    // 回复消息
                    $result = $minaService->customerServerSend($message, $openId);

                    // 打印错误日志
                    if (!$result) {
                        Tools::logInfo(print_r($result, 1));
                    }
                }
            }
        }
    }

    /**
     * 签名校验（第一次绑定消息推送url的时候，调用该方法）
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/8/6 12:26
     * @since PM_1.1_ws
     */
    private function valid()
    {
        $echoStr = $_GET["echostr"] ?? '';
        Tools::logInfo("echostr:" . print_r($echoStr, 1));

        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * 签名校验方法
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/8/6 12:27
     * @since PM_1.1_ws
     * @return bool
     */
    private function checkSignature()
    {
        $signature = $_GET["signature"] ?? '686a21f0768575e44128be9fee29ebf27f8c1839';
        $timestamp = $_GET["timestamp"] ?? '1522220526';
        $nonce = $_GET["nonce"] ?? '3689998994';
        $token = 'token';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        Tools::logInfo("signature:" . print_r($signature, 1));
        Tools::logInfo("timestamp:" . print_r($timestamp, 1));
        Tools::logInfo("nonce:" . print_r($nonce, 1));
        Tools::logInfo("tmpStr:" . print_r($tmpStr, 1));

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

}