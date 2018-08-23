<?php

namespace App\Http\Controllers;

class WebController extends Controller
{

    // 加群页面
    public function joinGroupView()
    {
        return View('join_group');
    }

    // 加群页面
    public function wxSettingView()
    {
        return View('wx-setting');
    }

    // 模拟微信登录要用
    public function loginWxmock()
    {
        $session = request('session', 123);
        return View('login-wxmock', compact('session'));
    }
}
