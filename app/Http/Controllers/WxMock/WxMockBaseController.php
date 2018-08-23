<?php

namespace App\Http\Controllers\WxMock;


use App\Http\Controllers\Controller;

class WxMockBaseController extends Controller
{
    protected $config;

    public function __construct($session = null)
    {
        $this->config = config('vbot');

        if ($session) {
            $this->config['session'] = $session;
        }
    }
}
