<?php

namespace Dixie\EloquentModelFuture;

use Illuminate\Database\Eloquent\Model;
use Dixie\EloquentModelFuture\Models\Future;
use Dixie\EloquentModelFuture\Contracts\ModelFuture;
use Carbon\Carbon;

class FuturePlanner
{
    /**
     * The model under action.
     *
     * @var Dixie\EloquentModelFuture\Contracts\ModelFuture
     */
    protected $model;

    /**
     * A new future instance that is about to get planned.
     *
     * @var Dixie\EloquentModelFuture\Models\Future
     */
    protected $newFuture;

    /**
     * The base query for getting futures.
     *
     * @var Illuminate\Database\Eloquent\Relations\MorphMany
     */
    protected $futures;

    /**
     * Create a new FuturePlanner instance.
     *
     * @param ModelFuture $model
     */
    public function __construct(ModelFuture $model)
    {
        $this->model = $model;
        $this->futures = $this->model->uncommittedFutures();
        $this->newFuture = new Future;
    }

    /**
     * Set the given attributes on the future model.
     *
     * @param array $attributes
     *
     * @return Dixie\EloquentModelFuture\FuturePlanner
     */
    public function plan(array $attributes)
    {
        $this->newFuture->data = $attributes;

        return $this;
    }

    /**
     * Set the date for when future should be committed.
     *
     * @param Carbon $futureDate
     *
     * @return Dixie\EloquentModelFuture\Models\Future
     */
    public function for(Carbon $futureDate)
    {
        $this->newFuture->commit_at = $futureDate;

        $this->newFuture->futureable()->associate($this->model);

        $this->newFuture->creator()->associate(auth()->id());

        $this->newFuture->save();

        return $this->newFuture;
    }

    /**
     * See how the user looks at the given date as a result,
     * of the futures planned for the given date.
     *
     * @param Carbon $futureDate
     *
     * @return Dixie\EloquentModelFuture\Contracts\ModelFuture
     */
    public function see(Carbon $futureDate)
    {
        return $this->getPlansFor($futureDate)->result();
    }

    /**
     * Get all future plans for a model.
     *
     * @return Dixie\EloquentModelFuture\Collections\FutureCollection
     */
    public function getPlans()
    {
        return $this->futures->get();
    }

    /**
     * Get the future plans for a given date.
     *
     * @param Carbon $futureDate
     *
     * @return Dixie\EloquentModelFuture\Collections\FutureCollection
     */
    public function getPlansFor(Carbon $futureDate)
    {
        return $this->futures->forDate($futureDate)->get();
    }

    /**
     * Get the future plans ranging from today to a given date.
     *
     * @param Carbon $futureDate
     *
     * @return Dixie\EloquentModelFuture\Collections\FutureCollection
     */
    public function getPlansUntil(Carbon $futureDate)
    {
        return $this->futures->untilDate($futureDate)->get();
    }

    /**
     * Check whether the model has any future plans.
     *
     * @return boolean
     */
    public function hasAnyPlans()
    {
        return (bool) $this->getPlans()->count();
    }

    /**
     * Check whether the model has any future plans for the given date.
     *
     * @param Carbon $futureDate
     *
     * @return boolean
     */
    public function hasAnyPlansFor(Carbon $futureDate)
    {
        return (bool) $this->getPlansFor($futureDate)->count();
    }

    /**
     * Check whether the model has any future plans
     * ranging from today to the given date.
     *
     * @param Carbon $futureDate
     *
     * @return bool
     */
    public function hasAnyPlansUntil(Carbon $futureDate)
    {
        return (bool) $this->getPlansUntil($futureDate)->count();
    }

}
