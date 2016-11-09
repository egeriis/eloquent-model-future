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

    public function uncommittedFutures()
    {
        return $this->futures()->whereNull('committed');
    }

    public function future()
    {
        return new FuturePlanner($this);
    }

    public function commit()
    {
        $date = Carbon::now();

        $this->future()->getPlansFor($date)->each(function($futurePlan) use ($date) {
            $futurePlan->update([
                'committed' => $date
            ]);
        });

        return $this->save();
    }
}
