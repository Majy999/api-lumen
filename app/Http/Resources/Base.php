<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Base extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $this->resource = collect($this->resource);
        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'status' => 'success',
            'status_code' => 200,
            'error' => 0,
            'code' => 0
        ];
    }
}
