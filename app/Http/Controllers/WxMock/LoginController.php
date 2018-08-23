<?php

namespace App\Http\Controllers\WxMock;

use App\Helpers\Tools;
use Hanson\Vbot\Foundation\Vbot as Bot;
use Illuminate\Support\Facades\Redis;

class LoginController extends WxMockBaseController
{
    /**
     * 获取二维码
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/8/23 20:07
     * @since PM_1.0_agent
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQrcode()
    {
        $session = request('session');
        Redis::lpush('wxmock', $session);
        system(env('ROOT_PATH') . '/public/wxmock.sh');
        return $this->response(Tools::success('获取二维码成功，请刷新该界面'));
    }
}
