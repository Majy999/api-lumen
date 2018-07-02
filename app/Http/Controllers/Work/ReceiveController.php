<?php

namespace App\Http\Controllers\Work;

use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\WeChatService;
use Extend\WorkWechat\Server\WXBizMsgCrypt;
use Illuminate\Support\Facades\Redis;

class ReceiveController extends Controller
{
    // 数据回调URL
    public function dataReceive()
    {
        // 企业号在公众平台上设置的参数如下
        $corpId = "ww8254a365bf92e5aa";

        $suiteId = [
            'ww85afb6954f398bde' => [
                'suiteid' => 'ww85afb6954f398bde',
                'suite_token' => 'FRLiucjHsmi8t9',
                'suite_encoding_aes_key' => 'vwvYPSPikSxymLof4Ri7RAzVfchzZHv7VTgkifcV18k',
            ],
        ];

        $msgSignature = request('msg_signature');
        $timestamp = request('timestamp');
        $nonce = request('nonce');
        $echostr = request('echostr');

        //如果是接入验证
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($echostr)) {

            $sEchoStr = "";
            foreach ($suiteId as $k => $v) {
                $wxcpt = new WXBizMsgCrypt($v['suite_token'], $v['suite_encoding_aes_key'], $corpId);
                $errCode = $wxcpt->VerifyURL($msgSignature, $timestamp, $nonce, $echostr, $sEchoStr);//VerifyURL方法的最后一个参数是带取地址的,

                //如果err_code === 0 的时候, $sEchoStr肯定不是""
                if ($errCode == 0) {
                    echo $sEchoStr;
                    exit;
                } else {
                    print("ERR: " . $errCode . "\n\n");
                    exit;
                }
            }
        } //如果是微信推送消息
        else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // post请求的密文数据
            $sReqData = file_get_contents("php://input"); //必须通过输入流方式获取post数据, 数据头为{"Content-Type":"application/xml"}
            $xml = new \DOMDocument();
            $xml->loadXML($sReqData);
            $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;//其实就是目标套件的suiteid
            $ToUserNameType = '';

            $sassInfo = array();//存储当前推送回调这个套件的信息, 用来实例化
            foreach ($suiteId as $k => $v) {
                if ($v['suite_id'] == $ToUserName) {
                    $sassInfo = $v;
                    $ToUserNameType = 'sutieid';
                    break;
                }
            }

            //如果以上for循环不能得到套件结果, 说明不是回调接口来的请求, ToUserName对应的肯定是一个普通企业的corpid, 这时还要通过Agentid参数来获得
            //到底是哪个应用来的请求数据
            if (empty($sassInfo)) {
                //通过corpid和Agentid反推来得到到底是哪个套件, 因为实例化解密类的时候, 需要token和encoding_aes_key
                $ToUserNameType = 'corpid';
            }

            $wxcpt = new WXBizMsgCrypt($sassInfo['suite_token'], $sassInfo['suite_encoding_aes_key'], $ToUserName);
            $errCode = -1;
            $sMsg = "";  // 解析之后的明文
            $errCode = $wxcpt->DecryptMsg($msgSignature, $timestamp, $nonce, $sReqData, $sMsg);//VerifyURL方法的最后一个参数是带取地址的,
            if ($errCode == 0) {
                if ($ToUserNameType == 'sutieid') {
                    $this->exceDec($sMsg);
                } else if ($ToUserNameType == 'corpid') {
                    $this->exceDecInfo($sMsg);
                }

            }
        }//这种情况是在服务商辅助授权方式授权的应用, 微信没有回调, 只会在回调url里面有auth_code这个参数, 也就是临时授权码, 这样就相当于模拟了一个请求, 交给相同的方法来处理授权
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($get['auth_code'])) {
            $authCode = $get['auth_code'];
            $state = $get['state'];//var_dump($state);
            $suiteId = '';//解密$state获得suiteid, 在授权请求的state参数, 把suiteid加密了
            $time = time();
            $sMsg = '';

            $this->exceDec($sMsg, 'server');
        } else {
            Tools::logInfo("otherrequest\n\n");
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

    /**
     * 解析内容
     */
    public function exceDec($sMsg, $type = 'online')
    {
        $xml = new \DOMDocument();
        $xml->loadXML($sMsg);
        $suite_id = $xml->getElementsByTagName('SuiteId')->item(0)->nodeValue;
        $info_type = $xml->getElementsByTagName('InfoType')->item(0)->nodeValue;
        $echoStr = 'success';
        switch ($info_type) {
            case 'suite_ticket'://推送suite_ticket协议每十分钟微信推送一次
                $suite_ticket = $xml->getElementsByTagName('SuiteTicket')->item(0)->nodeValue;

                if (!empty($suite_ticket)) {
                    Tools::logInfo($suite_ticket);
                    Redis::set('suite_ticket', $suite_ticket);
                } else {
                    //错误信息
                }
                break;

            case 'change_auth'://变更授权的通知 需要调用 获取企业号的授权信息, 更改企业号授权信息
                $auth_corp_id = $xml->getElementsByTagName('AuthCorpId')->item(0)->nodeValue;//普通企业的corpid
                //业务需求
                break;

            case 'cancel_auth'://取消授权的通知 -- 特指套件取消授权
                $auth_corp_id = $xml->getElementsByTagName('AuthCorpId')->item(0)->nodeValue;//普通企业的corpid
                //自己的业务需求
                break;

            case 'create_auth'://授权成功推送auth_code事件
                //获取AuthCode
                $auth_code = $xml->getElementsByTagName('AuthCode')->item(0)->nodeValue;
                if (!empty($auth_code)) {

                    //服务商辅助授权方式安装应用
                    if ('online' !== $type && 'server' === $type) {

                    } //线上自助授权安装应用
                    else if ('online' == $type) {

                    }
                }
                break;
            default:
                break;
        }
        echo $echoStr;
    }

    /**
     * 解析事件推送和普通消息推送
     * @param String(xml) $sMsg
     */
    public function exceDecInfo($sMsg)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($sMsg);
        $toUserName = $corpid = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
        $fromUserName = $userid = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
        $msgType = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
        $agentID = $xml->getElementsByTagName('AgentID')->item(0)->nodeValue;

        //TODO
        $data = array(
            'corpid' => $corpid,
            'userid' => $userid,
            'msgType' => $msgType,
            'agentid' => $agentID,
        );
    }


}
