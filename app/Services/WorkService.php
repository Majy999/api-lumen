<?php

namespace App\Services;

use App\Exceptions\RequestException;
use App\Helpers\HttpUtils;
use App\Helpers\RedisTools;
use App\Helpers\Tools;
use App\Models\MerchantSuite;
use Illuminate\Support\Facades\Redis;

class WorkService
{
    private $corpId;
    private $suiteId;
    private $suiteIds;

    public function __construct()
    {
        // 企业号在公众平台上设置的参数如下
        $this->corpId = "ww0b513d721649b0fb";

        // 第三方应用配置
        $this->suiteIds = [
            // 测试应用
            'wwe4780336c1c3912c' => [
                'suite_id' => 'wwe4780336c1c3912c',
                'suite_secret' => 'JsBawxl9KprAJfpNP6aKAlZLZOhULphFwiJHbUv9eD4',
                'suite_token' => 'QDjovOd7q3h6Rf55hfDvPA',
                'suite_encoding_aes_key' => '0AHCixgqg2I7ksN2yxd5qycU01cdiC8bb6CwBUS9Gie',
            ],
        ];

        $this->suiteId = 'wwe4780336c1c3912c';
    }

    /**
     * 获取第三方应用凭证 suite_access_token
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/6 17:29
     * @param $suiteId
     * @since PM_1.0_zantui
     * @return array
     * @throws RequestException
     */
    public function getSuiteAccessToken($suiteId)
    {
        // 从缓存中获取第三方应用凭证 suite_access_token
        $suiteAccessTokenRedisKey = 'suite_access_token:' . $suiteId;
        $suiteAccessToken = Redis::get($suiteAccessTokenRedisKey);

        if ($suiteAccessToken) {
            return Tools::setData($suiteAccessToken);
        } else {
            if (!$suiteId) throw new RequestException('请传入suite_id');

            if (!isset($this->suiteIds[$suiteId])) throw new RequestException('请传入正确的suite_id');

            // 获取Redis中存储的 suite_ticket
            $suiteTicket = Redis::get('suite_ticket:' . $suiteId);

            if (!$suiteTicket) throw new RequestException('未获取到正确的suite_ticket');

            // 获取配置信息
            $suiteconfig = $this->suiteIds[$suiteId];

            $args = [
                'suite_id' => $suiteconfig['suite_id'],
                'suite_secret' => $suiteconfig['suite_secret'],
                'suite_ticket' => $suiteTicket,
            ];

            $url = HttpUtils::MakeUrl("/cgi-bin/service/get_suite_token");
            $json = HttpUtils::httpPostParseToJson($url, $args);

            if (isset($json['suite_access_token'])) {
                Redis::set($suiteAccessTokenRedisKey, $json['suite_access_token']);
                Redis::expire($suiteAccessTokenRedisKey, $json['expires_in']);
                return Tools::setData($json['suite_access_token']);
            } else {
                Tools::logError(json_encode($json));
                if (isset($json['errmsg'])) {
                    throw new RequestException($json['errmsg']);
                } else {
                    throw new RequestException('请传入正确的suite_id');
                }
            }
        }
    }

    /**
     * 获取预授权码 pre_auth_code
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/6 17:32
     * @since PM_1.0_zantui
     * @return array
     * @throws RequestException
     */
    public function getPreAuthCode($suiteId)
    {
        // 从缓存中获取授权码 pre_auth_code
        $preAuthCodeRedisKey = 'pre_auth_code:' . $suiteId;
        $preAuthCode = Redis::get($preAuthCodeRedisKey);

        if ($preAuthCode) {
            return Tools::setData($preAuthCode);
        } else {
            // 获取第三方应用凭证
            $suiteAccessToken = $this->getSuiteAccessToken($suiteId);
            if ($suiteAccessToken['error'] == 1) {
                return Tools::error($suiteAccessToken['message']);
            } else {
                $suiteAccessToken = $suiteAccessToken['data'];
            }

            if (!empty($suiteAccessToken)) {
                $url = HttpUtils::MakeUrl("/cgi-bin/service/get_pre_auth_code?suite_access_token=" . $suiteAccessToken);
                $json = HttpUtils::httpGetParseToJson($url);
                if (isset($json['pre_auth_code'])) {
                    Redis::set($preAuthCodeRedisKey, $json['pre_auth_code']);
                    Redis::expire($preAuthCodeRedisKey, $json['expires_in']);
                    return Tools::setData($json['pre_auth_code']);
                } else {
                    throw new RequestException($json['errmsg']);
                }
            } else {
                throw new RequestException('获取第三方应用凭证不能为空');
            }
        }
    }

    /**
     * 获取永久授权码
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/13 14:43
     * @param $suiteId
     * @since PM_1.0_zantui
     * @return array
     * @throws RequestException
     */
    private function getPermanentCode($suiteId)
    {
        // 永久授权码redisKey
        $permanentCodeRedisKey = 'permanent_code:suite_id:' . $suiteId;
        $permanentCode = Redis::get($permanentCodeRedisKey);

        if ($permanentCode) {
            return Tools::setData($permanentCode);
        } else {
            throw new RequestException('获取永久授权码失败');
        }
    }

    /**
     * 获取企业access_token
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/6 18:36
     * @param $suiteId
     * @since PM_1.0_zantui
     * @return array
     * @throws RequestException
     */
    public function getCropAccessToken($suiteId)
    {
        // 企业access_token redisKey
        $corpAccessTokenRedisKey = 'corp_access_token:suite_id:' . $suiteId;
        $corpAccessToken = Redis::get($corpAccessTokenRedisKey);

        if ($corpAccessToken) {
            return Tools::setData($corpAccessToken);
        } else {

            $authCorpidRedisKey = 'auth_corp_id:suite_id:' . $suiteId;
            $authCorpid = Redis::get($authCorpidRedisKey);

            if (!$authCorpid) throw new RequestException('获取企业的corpid失败');

            // 获取第三方应用凭证
            $suiteAccessToken = $this->getSuiteAccessToken($suiteId);
            if ($suiteAccessToken['error'] == 1) {
                throw new RequestException($suiteAccessToken['message']);
            } else {
                $suiteAccessToken = $suiteAccessToken['data'];
            }

            // 获取永久授权码
            $permanentCode = $this->getPermanentCode($suiteId);
            if ($permanentCode['error'] == 1) {
                return Tools::error($permanentCode['message']);
            } else {
                $permanentCode = $permanentCode['data'];
            }

            $args = [
                'auth_corpid' => $authCorpid,
                'permanent_code' => $permanentCode
            ];

            $url = HttpUtils::MakeUrl("/cgi-bin/service/get_corp_token?suite_access_token=" . $suiteAccessToken);
            $json = HttpUtils::httpPostParseToJson($url, $args);

            if (isset($json['access_token'])) {
                Redis::set($corpAccessTokenRedisKey, $json['access_token']);
                Redis::expire($corpAccessTokenRedisKey, $json['expires_in']);
                return Tools::setData($json['access_token']);
            } else {
                Tools::logError(json_encode($json));
                throw new RequestException($json['errmsg']);
            }
        }
    }

    /**
     * 第三方根据code获取企业成员信息
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 20:31
     * @param $suiteId
     * @param $code
     * @since PM_1.0_zantui
     * @return array
     * @throws RequestException
     */
    public function getUserinfo3rd($suiteId, $code)
    {
        // 获取第三方应用凭证
        $suiteAccessToken = $this->getSuiteAccessToken($suiteId);
        if ($suiteAccessToken['error'] == 1) {
            throw new RequestException($suiteAccessToken['message']);
        } else {
            $suiteAccessToken = $suiteAccessToken['data'];
        }

        $url = HttpUtils::MakeUrl("/cgi-bin/service/getuserinfo3rd?access_token=" . $suiteAccessToken . "&code=" . $code);
        $json = HttpUtils::httpGetParseToJson($url);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 发送企业微信消息
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 10:48
     * @param $userId
     * @param string $content
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function sendMessage($userId, $content = 'test content')
    {
        if (empty($content)) return Tools::error('发送企业微信消息：content 参数不能为空');

        // 获取应用id
        $agentidRedisKey = 'agentid:suite_id:' . $this->suiteId;
        $agentId = Redis::get($agentidRedisKey);
        if (!$agentId) return Tools::error('获取不到agent_id');

        // 封装发送体
        $args = [
            'touser' => $userId,
            'toparty' => '',
            'totag' => '',
            'msgtype' => 'text',
            'agentid' => $agentId,
            'text' => [
                'content' => $content
            ],
            'safe' => 0
        ];

        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            return Tools::error($cropAccessToken['message']);
        }

        $url = HttpUtils::MakeUrl("/cgi-bin/message/send?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 获取部门列表
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:16
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function departmentList()
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $url = HttpUtils::MakeUrl("/cgi-bin/department/list?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpGetParseToJson($url);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 创建部门
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:16
     * @param $departmentData
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function createDepartment($departmentData)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $args = [
            'name' => $departmentData['name'],
            'parentid' => 1,// 默认商家id为1
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/department/create?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) {
            Tools::logInfo($args, '创建部门参数');
            Tools::logInfo($json['errmsg'], '创建部门结果');
            // throw new RequestException($json['errmsg']);
        }
        return $json;
    }

    /**
     * 更新部门
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:52
     * @param $data
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function updateDepartment($data)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $args = [
            'id' => $data['department_id'],
            'name' => $data['name'] ?? ''
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/department/update?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 员工列表
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:20
     * @param int $departmentId 部门id
     * @param int $fetchChild 1/0：是否递归获取子部门下面的成员
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function userList($departmentId = 1, $fetchChild = 1)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $url = HttpUtils::MakeUrl("/cgi-bin/user/list?access_token=" . $cropAccessToken['data'] . '&department_id=' . $departmentId . '&fetch_child=' . $fetchChild);
        $json = HttpUtils::httpGetParseToJson($url);

        return $json;
    }

    /**
     * 创建用户
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:36
     * @param $data
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function createUser($data)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $args = [
            'userid' => $data['mobile'] ?? '',
            'name' => $data['name'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'department' => $data['department'] ?? [1], // 默认部门为1
            'position' => $data['position'] ?? '',
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/user/create?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        return $json;
    }

    /**
     * 发送邀请
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/8 16:21
     * @param $userIds
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function inviteUser($userIds)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            return Tools::error($cropAccessToken['message']);
        }

        $args = [
            'user' => $userIds
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/batch/invite?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 删除成员
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:54
     * @param $useridList
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function deleteUser($useridList)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $args = [
            'useridlist' => $useridList
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/user/batchdelete?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 更新成员
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/7 14:52
     * @param $data
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function updateUser($data)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $args = [
            'userid' => $data['out_employee_id'],
            'name' => $data['name'],
            // 'mobile' => $data['mobile'],
            'department' => $data['department'] ?? [1], // 默认部门为1
            'position' => $data['position'],
        ];

        $url = HttpUtils::MakeUrl("/cgi-bin/user/update?access_token=" . $cropAccessToken['data']);
        $json = HttpUtils::httpPostParseToJson($url, $args);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

    /**
     * 获取用户信息
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/7/11 16:26
     * @param $userId
     * @since PM_1.0_zantui
     * @return array|mixed
     * @throws RequestException
     */
    public function getUser($userId)
    {
        $cropAccessToken = $this->getCropAccessToken($this->suiteId);
        if ($cropAccessToken['error'] == 1) {
            throw new RequestException($cropAccessToken['message']);
        }

        $url = HttpUtils::MakeUrl("/cgi-bin/user/get?access_token=" . $cropAccessToken['data'] . '&userid=' . $userId);
        $json = HttpUtils::httpGetParseToJson($url);

        if ($json['errcode'] != 0) throw new RequestException($json['errmsg']);
        return $json;
    }

}