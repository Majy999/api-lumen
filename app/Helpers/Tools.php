<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class Tools
{
    /**
     * 写入成功返回
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date   2018/6/27 15:32
     *
     * @param string $message
     * @param int    $code
     *
     * @return array
     */
    public static function success($message = '写入成功', $code = 0)
    {
        $response = [
            'status' => 'success',
            'status_code' => 200,
            'error' => 0,
            'code' => $code,
            'message' => $message
        ];
        return $response;
    }

    /**
     * 写入成功返回
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date   2018/6/27 15:32
     *
     * @param array $data
     * @param int   $code
     *
     * @return array
     */
    public static function setData($data = [], $code = 0)
    {
        $response = [
            'status' => 'success',
            'status_code' => 200,
            'error' => 0,
            'code' => $code,
            'data' => $data
        ];
        return $response;
    }

    /**
     * 写入失败返回
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date   2018/6/27 15:32
     *
     * @param string $message
     * @param int    $code
     *
     * @return array
     */
    public static function error($message = '写入失败', $code = 0)
    {
        $response = [
            'status' => 'failed',
            'status_code' => 500,
            'error' => 1,
            'code' => $code,
            'message' => $message
        ];
        return $response;
    }

    /**
     * logInfo
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date   xxx
     *
     * @param      $content
     * @param null $title
     *
     * @since  PM_1.0_agent
     */
    public static function logInfo($content, $title = null)
    {
        if (env('LOG_ON', true)) {
            if ($title) Log::info($title);
            Log::info('==========================');
            Log::info(print_r($content, true));
            Log::info('==========================');
        }
    }

    /**
     * 单个错误日志输出
     *
     * @param string $content
     */
    public static function logError($content)
    {
        Log::error('**************************');
        Log::error($content);
        Log::error('**************************');
    }

    /**
     * 事务异常错误日志输出
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date   2018/6/27 15:32
     *
     * @param \Exception $exception
     */
    public static function logUnusualError(\Exception $exception)
    {
        Log::error('**************************');
        Log::error(print_r($exception->getFile(), true));
        Log::error(print_r($exception->getLine(), true));
        Log::error(print_r($exception->getMessage(), true));
        Log::error('**************************');
    }

    /**
     * 多个日志一次性输出
     *
     * @author Majy999 <Majy999@outlook.com>
     * @date   2018/6/27 15:32
     *
     * @param      $content
     * @param null $subject
     *
     * @return bool
     */
    static public function singleLog($content, $subject = null)
    {
        if (!isset($GLOBALS['debugArray'])) {
            $GLOBALS['debugArray'] = array();
        }

        if ($subject) {
            array_push($GLOBALS['debugArray'], $subject);
            array_push($GLOBALS['debugArray'], '==========================');
        }

        if ($content) {
            array_push($GLOBALS['debugArray'], $content);
            array_push($GLOBALS['debugArray'], '--------------------------');
        }

        return true;
    }

    /**
     * curlRequest
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date   2018/11/22 10:09
     *
     * @param        $url
     * @param string $post
     * @param string $cookie
     * @param int    $returnCookie
     *
     * @since  PM_1.2_sibu
     * @return bool|mixed
     */
    public static function curlPost($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }
}