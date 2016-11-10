<?php

namespace Dixie\LaravelModelFuture\Contracts;

interface ModelFuture
{

    /**
     * Defines the relationship between the model and its futures
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function futures();

}
