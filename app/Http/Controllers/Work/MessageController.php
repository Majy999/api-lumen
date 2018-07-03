<?php

namespace App\Http\Controllers\Work;

use App\Helpers\HttpUtils;
use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Services\WeChatService;
use Extend\WorkWechat\Server\WXBizMsgCrypt;
use Illuminate\Support\Facades\Redis;

class MessageController extends Controller
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
            'ww65ff1d66710fd8c5' => [
                'suite_id' => 'ww65ff1d66710fd8c5',
                'suite_secret' => '_yRuEL2YtRYjJuSqZo2nu-mNd3UzSFCmovwDghW7bsQ',
                'suite_token' => 'MUtPUemV6R9r3',
                'suite_encoding_aes_key' => 'sfOnSaNgwxLFHM90KwrKzMRTG8jnMccyMsRGZYSOo4V',
            ],
            // 赞推
            'ww85afb6954f398bde' => [
                'suite_id' => 'ww85afb6954f398bde',
                'suite_secret' => 'FIVQwHW4SJ_SqlAH9SwjVVEJku_Qkc8PbeGtA8lPR84',
                'suite_token' => 'FRLiucjHsmi8t9',
                'suite_encoding_aes_key' => 'vwvYPSPikSxymLof4Ri7RAzVfchzZHv7VTgkifcV18k',
            ]
        ];
    }

    // 获取企业授权信息
    public function getPermanentCode()
    {
        $corpId = request('corp_id', 'ww8254a365bf92e5aa');
        $suiteId = request('suite_id', 'ww85afb6954f398bde');
        $permanentCode = Redis::get('permanent_code_suite_id:' . $suiteId . ':corp_id:' . $corpId);
        return Tools::setData($permanentCode);
    }
}
