<?php

namespace Dixie\LaravelModelFuture\Collections;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class FutureCollection extends EloquentCollection
{
    public function before()
    {
        return $this->transform(function($item) {
            return $item->futureable;
        });;
    }

    public function after()
    {
        $model = $this->first()->futureable;

        return $this->reduce(function($carry, $item) {
            return $carry->forceFill($item->data);
        }, $model);
    }

    public function resultDiff()
    {
        return $this->map(function($item) {
            $originalModel = $item->futureable;

            $diffCollection = collect($item->data)
                ->map(function($value, $key) use ($originalModel) {
                    return [
                        'before' => $originalModel->{$key},
                        'after' => $value
                    ];
                });

            return $diffCollection->put('commit_at', $item->commit_at);
        });
    }
}
