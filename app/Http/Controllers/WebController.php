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
}
