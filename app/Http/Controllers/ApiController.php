<?php

namespace App\Http\Controllers;

use App\Helpers\Tools;

class ApiController extends Controller
{
    public function get()
    {
        $data = [
            'name' => 'jy',
            'age' => 12,
        ];
        return $this->response(Tools::setData($data));
    }
}
