<?php

namespace Dixie\LaravelModelFuture;

use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Models\Future;
use Dixie\LaravelModelFuture\Contracts\ModelFuture;
use Carbon\Carbon;

class FuturePlan
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
        $data = json_encode($this->attributes);
        $future = new Future();

        $future->fill([
            'data' => $data,
            'commit_at' => $futureDate,
        ]);

        return $this->model
            ->futures()
            ->save($future);
    }

}
