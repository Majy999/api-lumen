<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
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
