<?php

namespace App\Http\Controllers;

class WebController extends Controller
{

    // 加群页面
    public function joinGroupView()
    {
        return View('join_group');
    }
}
