<?php

namespace App\Models;

class Test extends Base
{
    protected $table = 'test';

    protected $fillable = [
        'name',
        'desc'
    ];
}