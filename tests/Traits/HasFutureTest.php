<?php

namespace Dixie\LaravelModelFuture\Tests\Traits;

use Dixie\LaravelModelFuture\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Dixie\LaravelModelFuture\FuturePlanner;
use Carbon\Carbon;

class HasFutureTest extends TestCase
{
    public function testItDefinesAMorphManayRelationshipCalledFutures()
    {
        $user = $this->createUser();

        $this->assertInstanceOf(MorphMany::class, $user->futures());
    }

    public function testItMakesTheuserAbleToPlanUsingAFuturePlannerInstance()
    {
        $user = $this->createUser();

        $this->assertInstanceOf(FuturePlanner::class, $user->future());
    }

    public function testItCanCommitToAState()
    {
        $user = $this->createUser();
        $today = Carbon::now();
        $tomorrow = Carbon::now()->addDay();

        $future1 = $this->createFuturePlanFor($user, $today);
        $future2 = $this->createFuturePlanFor($user, $tomorrow);

        $this->assertTrue(
            $user->future()->see($today)->commit()
        );
        $this->assertEquals($future1->fresh()->committed_at, Carbon::now());
        $this->assertNull($future2->fresh()->committed_at);
    }
}

