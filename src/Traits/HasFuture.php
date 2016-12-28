<?php

namespace Dixie\EloquentModelFuture\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Dixie\EloquentModelFuture\Models\Future;
use Dixie\EloquentModelFuture\FuturePlanner;

trait HasFuture
{
    /**
     * Defines the relationship between the model and its futures.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function futures()
    {
        return $this->morphMany(
            Future::class,
            'futures',
            'futureable_type',
            'futureable_id'
        );
    }

    /**
     * Defines the relationship between the model and its uncommitted futures.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function uncommittedFutures()
    {
        return $this->futures()->whereNull('committed_at');
    }

    /**
     * Start planning the future of a model
     *
     * @return Dixie\EloquentModelFuture\FuturePlanner
     */
    public function future()
    {
        return new FuturePlanner($this);
    }

    /**
     * Commit to the presented result of the model
     *
     * @return boolean
     */
    public function commit()
    {
        $this->future()->getPlansFor(Carbon::now())
            ->each([$this, 'commitFuturePlan']);

        return $this->save();
    }

    /**
     * Commit the given future.
     *
     * @param boolean
     */
    public function commitFuturePlan(Future $futurePlan)
    {
        $futurePlan->committed_at = Carbon::now();

        return $futurePlan->save();
    }
}
