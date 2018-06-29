<?php

namespace App\Http\Controllers\Work;

use App\Helpers\Tools;
use App\Http\Controllers\Controller;

class ReceiveController extends Controller
{
    // 数据回调URL
    public function dataReceive()
    {
        $input = file_get_contents('php://input');
        Tools::logInfo('数据回调URL');
        Tools::logInfo($input);
    }

    // 指令回调URL
    public function handleReceive()
    {
        $input = file_get_contents('php://input');
        Tools::logInfo('指令回调URL');
        Tools::logInfo($input);
    }

}
