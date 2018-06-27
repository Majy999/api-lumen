<?php

namespace App\Http\Controllers;

use App\Helpers\Tools;
use App\Http\Resources\TestCollection;
use App\Models\Test;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function redis()
    {
        $data = [
            'name' => 'jy',
            'age' => 12,
        ];
        Redis::set('lumen', json_encode($data));
        Redis::expire('lumen', 600);
        return $this->response(Tools::setData($data));
    }

    public function mysql()
    {
        $data = [
            'name' => 'jy',
            'desc' => 'desc',
        ];

        $testModel = new Test();
        $testModel->fill($data);
        $testModel->save();

        return $this->response(Tools::setData($data));
    }

    public function mysqlGet()
    {
        $data = Test::get();
        return $this->response(new TestCollection($data));
    }
}
