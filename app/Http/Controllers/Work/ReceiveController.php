<?php

namespace App\Http\Controllers\Work;

use App\Helpers\HttpUtils;
use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\WeChatService;
use Extend\WorkWechat\Server\WXBizMsgCrypt;
use Illuminate\Support\Facades\Redis;

class ReceiveController extends Controller
{
    private $corpId;
    private $suiteIds;

    public function __construct()
    {
        // 企业号在公众平台上设置的参数如下
        $this->corpId = "ww8254a365bf92e5aa";

        // 第三方应用配置
        $this->suiteIds = [
            // 测试应用
            'wwe6e31391320c9631' => [
                'suite_id' => 'wwe6e31391320c9631',
                'suite_secret' => '9JYxf4OuGvRzbFPn8h-uoyPTKJubjw4BWbnrgHtkB-0',
                'suite_token' => 'AOCBP',
                'suite_encoding_aes_key' => 'nXnOuYHjfhvo54fGef99qeS8HxFu25it9TxdiR6QLkj',
            ],
        ];
    }

    // 数据回调URL
    public function dataReceive()
    {
        $msgSignature = request('msg_signature');
        $timestamp = request('timestamp');
        $nonce = request('nonce');
        $echostr = request('echostr');

        // 接入验证
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($echostr)) {

            $sEchoStr = "";
            foreach ($this->suiteIds as $k => $v) {
                $wxcpt = new WXBizMsgCrypt($v['suite_token'], $v['suite_encoding_aes_key'], $this->corpId);
                // VerifyURL方法的最后一个参数是带取地址的,
                $errCode = $wxcpt->VerifyURL($msgSignature, $timestamp, $nonce, $echostr, $sEchoStr);

                // 如果err_code === 0 的时候, $sEchoStr肯定不是""
                if ($errCode == 0) {
                    echo $sEchoStr;
                    exit;
                } else {
                    Tools::logError($errCode);
                }
            }
        } // 如果是微信推送消息
        else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // post请求的密文数据
            $sReqData = file_get_contents("php://input");
            $xml = new \DOMDocument();
            $xml->loadXML($sReqData);
            // 目标套件的suiteid
            $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
            $ToUserNameType = '';

            // 存储当前推送回调这个套件的信息, 用来实例化
            $sassInfo = [];
            foreach ($this->suiteIds as $k => $v) {
                if ($v['suite_id'] == $ToUserName) {
                    $sassInfo = $v;
                    $ToUserNameType = 'sutieid';
                    break;
                }
            }

            // 如果以上for循环不能得到套件结果, 说明不是回调接口来的请求, ToUserName对应的肯定是一个普通企业的corpid,
            // 这时还要通过Agentid参数来获得到底是哪个应用来的请求数据
            if (empty($sassInfo)) {
                // 通过corpid和Agentid反推来得到到底是哪个套件, 因为实例化解密类的时候, 需要token和encoding_aes_key
                $ToUserNameType = 'corpid';
            }

            $wxcpt = new WXBizMsgCrypt($sassInfo['suite_token'] ?? '', $sassInfo['suite_encoding_aes_key'] ?? '', $ToUserName);
            // 解析之后的明文
            $sMsg = '';
            // VerifyURL方法的最后一个参数是带取地址的
            $errCode = $wxcpt->DecryptMsg($msgSignature, $timestamp, $nonce, $sReqData, $sMsg);
            if ($errCode == 0) {
                if ($ToUserNameType == 'sutieid') {
                    $this->exceDec($sMsg);
                } else if ($ToUserNameType == 'corpid') {
                    $this->exceDecInfo($sMsg);
                }

            }
        }
        // 这种情况是在服务商辅助授权方式授权的应用, 微信没有回调, 只会在回调url里面有auth_code这个参数, 也就是临时授权码,
        // 这样就相当于模拟了一个请求, 交给相同的方法来处理授权
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty(request('auth_code'))) {
            $authCode = request('auth_code');
            $state = request('state');
            $state = explode(",", $state);
            // state格式[$suiteId,$corpId]
            $suiteId = $state[0] ?? '';
            $corpId = $state[1] ?? '';
            $time = time();
            $sMsg = <<<EOD
<xml>
  <SuiteId><![CDATA[$suiteId]]></SuiteId>
  <CorpId><![CDATA[$corpId]]></CorpId>
  <AuthCode><![CDATA[$authCode]]></AuthCode>
  <InfoType><![CDATA[create_auth]]></InfoType>
  <TimeStamp>$time</TimeStamp>
</xml>
EOD;

            $this->exceDec($sMsg, 'server');
        } else {
            Tools::logInfo("其他请求\n\n");
        }
    }

    /**
     * 解析内容
     */
    public function exceDec($sMsg, $type = 'online')
    {
        $xml = new \DOMDocument();
        $xml->loadXML($sMsg);
        $suiteId = $xml->getElementsByTagName('SuiteId')->item(0)->nodeValue;
        $infoType = $xml->getElementsByTagName('InfoType')->item(0)->nodeValue;
        $echoStr = 'success';
        switch ($infoType) {
            // 推送suite_ticket协议每十分钟微信推送一次
            case 'suite_ticket':
                $suiteTicket = $xml->getElementsByTagName('SuiteTicket')->item(0)->nodeValue;

                if (!empty($suiteTicket)) {
                    Tools::logInfo($suiteTicket);
                    Redis::set('suite_ticket:' . $suiteId, $suiteTicket);
                    Redis::expire('suite_ticket:' . $suiteId, 1800);
                } else {
                    // 错误信息
                }
                break;

            // 变更授权的通知 需要调用 获取企业号的授权信息, 更改企业号授权信息
            case 'change_auth':
                // 普通企业的corpid
                $authCorpId = $xml->getElementsByTagName('AuthCorpId')->item(0)->nodeValue;

                break;

            // 取消授权的通知 -- 特指套件取消授权
            case 'cancel_auth':
                // 普通企业的corpid
                $authCorpId = $xml->getElementsByTagName('AuthCorpId')->item(0)->nodeValue;

                break;

            // 授权成功推送auth_code事件
            case 'create_auth':
                // 获取AuthCode
                $authCode = $xml->getElementsByTagName('AuthCode')->item(0)->nodeValue;
                $corpId = $this->corpId;
                if (!empty($authCode)) {

                    // 服务商辅助授权方式安装应用
                    if ('online' !== $type && 'server' === $type) {

                        $suiteAccessToken = Redis::get('suite_access_token:' . $suiteId);
                        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_permanent_code?suite_access_token=" . $suiteAccessToken);
                        $args = [
                            'auth_code' => $authCode,
                        ];
                        $json = HttpUtils::HttpPostParseToJson($url, $args);
                        if (isset($json['permanent_code'])) {
                            // 永久授权码redisKey
                            $permanentCodeRedisKey = 'permanent_code:suite_id:' . $suiteId;
                            Redis::set($permanentCodeRedisKey, $json['permanent_code']);
                            Redis::expire($permanentCodeRedisKey, $json['expires_in']);
                            Tools::logInfo($json, '获取企业永久授权码成功');

                            // auth_corp_info
                            $authCorpidRedisKey = 'auth_corp_id:suite_id:' . $suiteId;
                            Redis::set($authCorpidRedisKey, $json['auth_corp_info']['corpid']);

                            // agentid
                            $agentidRedisKey = 'agentid:suite_id:' . $suiteId;
                            Redis::set($agentidRedisKey, $json['auth_info']['agent'][0]['agentid']);
                        } else {
                            Tools::logError(print_r($json, 1));
                            Tools::logError("获取企业永久授权码失败");
                        }
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

        // todo
        $data = array(
            'corpid' => $corpid,
            'userid' => $userid,
            'msgType' => $msgType,
            'agentid' => $agentID,
        );
    }

    /**
     * 获取accessToken
     *
     * @author Majy999 <Majy999@outlook.com>
     */
    public function getAccessToken()
    {
        $weChatService = new WeChatService();
        $accessToken = $weChatService->getAccessToken();
        echo $accessToken;
    }

    /**
     * 获取第三方应用凭证 suite_access_token
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date 2018/7/2 15:15
     */
    public function getSuiteAccessToken()
    {
        $suiteId = request('suite_id', 'ww85afb6954f398bde');

        // 获取配置信息
        $suiteconfig = $this->suiteIds[$suiteId];

        // 获取Redis中存储的 suite_ticket
        $suiteTicket = Redis::get('suite_ticket:' . $suiteId);

        $args = [
            'suite_id' => $suiteconfig['suite_id'],
            'suite_secret' => $suiteconfig['suite_secret'],
            'suite_ticket' => $suiteTicket,
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_suite_token");
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if (isset($json['suite_access_token'])) {
            Redis::set('suite_access_token:' . $suiteId, $json['suite_access_token']);
            Redis::expire('suite_access_token:' . $suiteId, $json['expires_in']);
            return Tools::setData($json);
        } else {
            Tools::logError(json_encode($json));
            return Tools::error('获取不到 suite_access_token');
        }
    }

    // 获取预授权码
    public function getPreAuthCode()
    {
        $suiteId = request('suite_id', 'ww85afb6954f398bde');

        // 获取第三方应用凭证
        $suiteAccessToken = Redis::get('suite_access_token:' . $suiteId);
        if (!empty($suiteAccessToken)) {
            $url = HttpUtils::MakeUrl("/cgi-bin/service/get_pre_auth_code?suite_access_token=" . $suiteAccessToken);
            $json = HttpUtils::httpGetParseToJson($url);
            if (isset($json['pre_auth_code'])) {
                Redis::set('pre_auth_code:' . $suiteId, $json['pre_auth_code']);
                Redis::expire('pre_auth_code:' . $suiteId, $json['expires_in']);
                return Tools::setData($json);
            } else {
                Tools::logError(json_encode($json));
                return Tools::error('获取预授权码pre_auth_code失败');
            }
        } else {
            return Tools::error('获取第三方应用凭证不能为空');
        }
    }


}
