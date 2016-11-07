<?php

namespace Dixie\LaravelModelFuture\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Dixie\LaravelModelFuture\Models\Future;
use Dixie\LaravelModelFuture\FuturePlanner;

trait HasFuture
{
    public function futures()
    {
        return $this->morphMany(
            Future::class, 
            'futures', 'futureable_type', 'futureable_id'
        );
    }

    public function future()
    {
        return new FuturePlanner($this);
    }

}
