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

    public function __construct(ModelFuture $model)
    {
        $this->model = $model;
    }

    public function plan(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function for(Carbon $futureDate)
    {
        $data = $this->attributes;
        $future = new Future();

        $future->fill([
            'data' => $data,
            'commit_at' => $futureDate,
        ]);

        return $this->model
            ->futures()
            ->save($future);
    }

    public function anyPlansFor(Carbon $futureDate)
    {
        return (bool) $this->model->futures()
            ->where('commit_at', $futureDate)
            ->count();
    }

    public function getPlansFor(Carbon $futureDate)
    {
        return $this->model->futures()
            ->where('commit_at', $futureDate)
            ->get();
    }

    public function getPlansUntill(Carbon $futureDate)
    {
         return $this->model->futures()
            ->where('commit_at', '<=', $futureDate)
            ->get();
    }

}
