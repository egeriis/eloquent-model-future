<?php

namespace Dixie\LaravelModelFuture\Models;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Collections\FutureCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

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

    public function newCollection(array $models = [])
    {
        return new FutureCollection($models);
    }

    public function scopeForDate(Builder $query, Carbon $date)
    {
        return $query->where('commit_at', $date);
    }

    public function scopeUntilDate(Builder $query, Carbon $date)
    {
        return $query->where('commit_at', '<=', $date);
    }

    public function futureable()
    {
        return $this->morphTo()
            ->with('futures');
    }
}
