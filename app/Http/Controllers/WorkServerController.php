<?php

namespace App\Http\Controllers;

use App\Helpers\HttpUtils;
use App\Helpers\Tools;
use App\Services\WorkService;
use Illuminate\Support\Facades\Redis;

class WorkServerController extends Controller
{
    private $corpId;
    private $suiteId;
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

        $this->suiteId = 'wwe6e31391320c9631';
    }

    /**
     * 企业号授权
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/5 17:39
     * @since PM_1.0_zantui
     */
    public function workServerAuthorization()
    {
        // new 企业微信服务
        $workService = new WorkService();

        // 获取 suite_access_token
        $suiteAccessToken = $workService->getSuiteAccessToken($this->suiteId);
        if ($suiteAccessToken['error'] == 1) return $this->response(Tools::error($suiteAccessToken['message']));

        // 获取 pre_auth_code
        $preAuthCode = $workService->getPreAuthCode($this->suiteId);
        if ($preAuthCode['error'] == 1) return $this->response(Tools::error($suiteAccessToken['message']));

        $response = [
            'suite_id' => $this->suiteId,
            'corp_id' => $this->corpId,
            'pre_auth_code' => $preAuthCode['data']
        ];
        return $this->response(Tools::setData($response));
    }

    /**
     * 服务商辅助授权方式安装应用 - 永久授权码绑定
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/5 17:55
     * @since PM_1.0_zantui
     */
    public function permanentCodeBind()
    {
        $authCode = request('auth_code');
        $state = request('state');
        $state = explode(",", $state);
        $suiteId = $state[0] ?? '';
        $corpId = $state[1] ?? '';

        // new 企业微信服务
        $workService = new WorkService();

        // 请求获取预授权码
        // 获取 suite_access_token
        $suiteAccessToken = $workService->getSuiteAccessToken($suiteId);
        if ($suiteAccessToken['error'] == 1) return $this->response(Tools::error($suiteAccessToken['message']));

        $url = HttpUtils::MakeUrl("/cgi-bin/service/get_permanent_code?suite_access_token=" . $suiteAccessToken['data']);
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

            $response = Tools::success('获取企业永久授权码成功');
        } else {
            Tools::logInfo($json, '获取企业永久授权码失败');
            $response = Tools::error('获取企业永久授权码失败');
        }
        return $this->response($response);
    }
}
