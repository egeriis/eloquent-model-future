<?php

namespace Dixie\LaravelModelFuture\Collections;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class FutureCollection extends EloquentCollection
{
    /**
     * Get the original model state
     *
     * @return Illuminate\Support\Collection
     */
    public function original()
    {
        return $this->map(function($item) {
            return $item->futureable;
        });;
    }

    /**
     * Gets the model back with all the future data filled.
     *
     * @return Dixie\LaravelModelFuture\Contracts\ModelFuture
     */
    public function result()
    {
        $model = $this->first()->futureable;

        return $this->reduce(function($carry, $item) {
            return $carry->forceFill($item->data);
        }, $model);
    }

    /**
     * Gets a list of all fields that would change, with both before and after.
     *
     * @return Illuminate\Support\Collection
     */
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
