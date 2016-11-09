<?php

namespace Dixie\LaravelModelFuture\Collections;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class FutureCollection extends EloquentCollection
{
    public function original()
    {
        return $this->transform(function($item) {
            return $item->futureable;
        });;
    }

    public function result()
    {
        $model = $this->first()->futureable;

        return $this->reduce(function($carry, $item) {
            return $carry->forceFill($item->data);
        }, $model);
    }

    public function resultDiff()
    {
        return $this->map(function($item) {

            $before = $item->futureable->first(array_keys($item->data));

            return [
                'before' => json_encode($before->toArray()),
                'after' => json_encode($item->data),
                'commit_at' => $item->commit_at,
            ];
        });
    }
}
