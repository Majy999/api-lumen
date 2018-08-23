<?php

namespace App\Http\Controllers;

class WebController extends Controller
{

    // 加群页面
    public function joinGroupView()
    {
        $session = request('session', 123);
        return View('join_group', compact('session'));
    }

    // 加群页面
    public function wxSettingView()
    {
        $session = request('session', 123);
        return View('wx-setting', compact('session'));
    }

    // 模拟微信登录要用
    public function loginWxmock()
    {
        $session = request('session', 123);
        return View('login-wxmock', compact('session'));
    }
}
