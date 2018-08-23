<?php

namespace App\Http\Controllers\Mina;

use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\MinaService;
use EasyWeChat\Kernel\Messages\Raw;
use Illuminate\Support\Facades\Redis;

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

            $sessionFromKey = "sessionFrom:$openId";

            // 点击客服按钮进入客服
            if ($event == 'user_enter_tempsession') {
                $sessionFrom = explode(',', $sessionFrom);
                Redis::set($sessionFromKey, $sessionFrom[0]);
                Redis::expire($sessionFromKey, 1500);
            } else {
                $sessionFrom = Redis::get($sessionFromKey);

                // 设置微信
                if ($content == '设置微信' || ($msgType == 'miniprogrampage' && $sessionFrom == '1')) {
                    $title = '集客';
                    $logo = 'https://img.jkweixin.com/defaults/b-image/page/icon-login-logo@2x.png';
                    $url = 'https://api.majy999.com/wx-setting?open_id=xxx' . $openId;
                    $message = new Raw('{
                        "touser": "' . $openId . '",
                        "msgtype": "link",
                        "link": {
                              "title": "' . $title . ': 上传机器人微信二维码",
                              "description": "上传机器人微信二维码",
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

                // 我要加群
                if ($content == '我要加群' || ($msgType == 'miniprogrampage' && $sessionFrom == '2')) {
                    $title = '集客';
                    $logo = 'https://img.jkweixin.com/defaults/b-image/page/icon-login-logo@2x.png';
                    $url = 'https://api.majy999.com/join-group';
                    $message = new Raw('{
                        "touser": "' . $openId . '",
                        "msgtype": "link",
                        "link": {
                              "title": "' . $title . ': 我要加群",
                              "description": "长按扫码添加好友加群",
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

                // 登陆
                if ($content == '我要登陆' || ($msgType == 'miniprogrampage' && $sessionFrom == '3')) {

                    $title = '集客';
                    $logo = 'https://img.jkweixin.com/defaults/b-image/page/icon-login-logo@2x.png';
                    $url = 'https://api.majy999.com/login-wxmock';
                    $message = new Raw('{
                        "touser": "' . $openId . '",
                        "msgtype": "link",
                        "link": {
                              "title": "' . $title . ': 请求登录",
                              "description": "请求扫码登录",
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
