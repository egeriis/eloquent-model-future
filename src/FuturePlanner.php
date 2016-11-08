<?php

namespace Dixie\LaravelModelFuture;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Models\Future;
use Dixie\LaravelModelFuture\Contracts\ModelFuture;
use Carbon\Carbon;

class FuturePlanner
{
    protected $model;

    protected $attributes;

    protected $futureQuery;

    public function __construct(ModelFuture $model)
    {
        $this->model = $model;
        $this->futureQuery = $this->model->uncommittedFutures();
    }

    public function plan(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function for(Carbon $futureDate)
    {
        $future = new Future([
            'data' => $this->attributes,
            'commit_at' => $futureDate,
        ]);

        $future->futureable()
            ->associate($this->model)
            ->save();

        return $future;
    }

    public function hasAnyPlans()
    {
        return (bool) $this->futureQuery->count();
    }

    public function hasAnyPlansFor(Carbon $futureDate)
    {
        return (bool) $this->futureQuery->forDate($futureDate)->count();
    }

    public function hasAnyPlansUntil(Carbon $futureDate)
    {
        return (bool) $this->futureQuery->untilDate($futureDate)->count();
    }

    public function getPlans()
    {
        return $this->futureQuery->get();
    }

    public function getPlansFor(Carbon $futureDate)
    {
        return $this->futureQuery->forDate($futureDate)->get();
    }

    public function getPlansUntil(Carbon $futureDate)
    {
        return $this->futureQuery->untilDate($futureDate)->get();
    }

}
