<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Lumen\Routing\Router;

class Test extends Base
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $url = $request->url();

        // 测试数据列表
        if ($url == route('mysql-get')) {
            $data = [
                'id' => $this['id'],
                'name' => $this['name'],
                'desc' => $this['desc'],
            ];
            return $data;
        }

        return parent::toArray($request);
    }
}
