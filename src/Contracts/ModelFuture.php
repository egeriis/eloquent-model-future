<?php

namespace Dixie\EloquentModelFuture\Contracts;

interface ModelFuture
{

    /**
     * Defines the relationship between the model and its futures
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function futures();

}
