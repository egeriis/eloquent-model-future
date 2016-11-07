<?php

namespace Dixie\LaravelModelFuture\Models;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Collections\FutureCollection;

class Future extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    protected $dates = [
        'commit_at', 'committed',
        'created_at', 'updated_at'
    ];

    public function futureable()
    {
        return $this->morphTo();
    }

    public function newCollection(array $models = [])
    {
        return new FutureCollection($models);
    }
}
