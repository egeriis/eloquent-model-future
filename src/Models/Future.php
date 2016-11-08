<?php

namespace Dixie\LaravelModelFuture\Models;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Collections\FutureCollection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Future extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    protected $dates = [
        'commit_at',
        'committed',
        'deleted_at',
    ];

    protected $fillable = [
        'futureable_id', 'futureable_type',
        'commit_at', 'data', 'committed',
    ];

    public function futureable()
    {
        return $this->morphTo()
            ->with('futures');
    }

    public function newCollection(array $models = [])
    {
        return new FutureCollection($models);
    }
}
