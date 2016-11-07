<?php

namespace Dixie\LaravelModelFuture\Models;

use Illuminate\Database\Eloquent\Model;

class Future extends Model
{
    protected $casts = [

    ];

    public function futureable()
    {
        return $this->morphTo();
    }
}
