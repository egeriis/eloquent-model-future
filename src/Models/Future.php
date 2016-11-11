<?php

namespace Dixie\LaravelModelFuture\Models;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Collections\FutureCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

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

    /**
     * The attributes that should be casted to Carbon dates.
     *
     * @var array
     */
    protected $dates = [
        'commit_at',
        'committed_at',
        'deleted_at',
    ];

    /**
     * Mass-assignable fields.
     *
     * @var array
     */
    protected $fillable = [
        'futureable_id', 'futureable_type',
        'commit_at', 'data', 'committed_at',
    ];

    /**
     * Override the original Eloquent collection.
     *
     * @param Dixie\LaravelModelFuture\Collections\FutureCollection
     */
    public function newCollection(array $models = [])
    {
        return new FutureCollection($models);
    }

    /**
     * Get the relationship to the associated model,
     * for which the future has been planned.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function futureable()
    {
        return $this->morphTo()
            ->with('futures');
    }

    /**
     * Get the relationship to user who created the future plan.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            'createe_user_id'
        );
    }

    /**
     * Narrow the scope of a query to only include futures for given date.
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param Carbon\Carbon $date
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate(Builder $query, Carbon $date)
    {
        return $query->whereDate('commit_at', $date->toDateString());
    }

    /**
     * Narrow the scope of a query to only include futures,
     * ranging from today to the given date.
     *
     * @param Builder $query
     * @param Carbon $date
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUntilDate(Builder $query, Carbon $date)
    {
        $today = Carbon::now()->toDateString();

        return $query->where('commit_at', '>=', $today)
            ->where('commit_at', '<=', $date);
    }

    /**
     * Narrow the scope of a query to only include uncommitted futures.
     *
     * @param Builder $query
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUncommitted(Builder $query)
    {
        return $query->whereNull('committed_at');
    }

    /**
     * Narrow the scope of a query to only include uncommitted futures.
     *
     * @param Builder $query
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommitted(Builder $query)
    {
        return $query->whereNotNull('committed_at');
    }
}
