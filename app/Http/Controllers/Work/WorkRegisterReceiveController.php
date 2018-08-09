<?php

namespace App\Http\Controllers\Work;

use App\Helpers\HttpUtils;
use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\WeChatService;
use Extend\WorkWechat\Server\WXBizMsgCrypt;
use Illuminate\Support\Facades\Redis;

class WorkRegisterReceiveController extends Controller
{
    private $corpId;
    private $corpIds;

    public function __construct()
    {
        // 企业号在公众平台上设置的参数如下
        $this->corpId = "ww8254a365bf92e5aa";

        // 第三方应用配置
        $this->corpIds = [
            // 测试应用
            'ww65ff1d66710fd8c5' => [
                'corp_id' => 'ww8254a365bf92e5aa',
                'provider_secret' => 'DECRC5DJVJ9YPLKRwr3gIFxSU-GPJKZiLDZJEH8b5ICPeLMZByh5X2FGzzSoLGn2',
                'token' => 'qD3G',
                'encoding_aeskey' => 'B86Tit4ekA3rnViRl5W3m6xtKCm4Ggb1ocRFiTEAgwq',
            ]
        ];
    }

    // 注册回调URL
    public function workRegisterReceive()
    {
        $msgSignature = request('msg_signature');
        $timestamp = request('timestamp');
        $nonce = request('nonce');
        $echostr = request('echostr');

        // 接入验证
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($echostr)) {

            $sEchoStr = "";
            foreach ($this->corpIds as $k => $v) {
                $wxcpt = new WXBizMsgCrypt($v['token'], $v['encoding_aeskey'], $v['corp_id']);
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
        } else {
            Tools::logInfo("其他请求" . PHP_EOL);
        }

        $sReqData = file_get_contents("php://input");
        $xml = new \DOMDocument();
        $xml->loadXML($sReqData);
        Tools::logInfo("其他请求" . PHP_EOL);
    }

}
