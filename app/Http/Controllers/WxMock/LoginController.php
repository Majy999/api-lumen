<?php

namespace App\Http\Controllers\WxMock;

use App\Helpers\Tools;
use Hanson\Vbot\Foundation\Vbot as Bot;
use Illuminate\Http\Request;
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
        return $this->response(Tools::success('获取二维码成功，请刷新该界面'));
    }

    /**
     * 提交图片
     *
     * @author Jy马 <Majy999@outlook.com>
     * @date 2018/8/23 21:26
     * @param Request $request
     * @since PM_1.0_agent
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadQrcode(Request $request)
    {
        $file = $request->file('file');
        $session = $request->get('session', 123);
        $allowed_extensions = ["png", "jpg", "gif"];
        if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
            return $this->response(Tools::error('只能传图片'));
        }

        if ($request->hasFile('file')) {

        }

        $destinationPath = env('ROOT_PATH') . '/public/image/userQrcode/';
        $extension = $file->getClientOriginalExtension();
        $fileName = $session . '.jpg';
        $result = $file->move($destinationPath, $fileName);
        if ($result) {
            echo '上传成功';
        } else {
            echo '上传失败';
        }
    }
}
