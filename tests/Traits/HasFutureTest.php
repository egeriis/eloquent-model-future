<?php

namespace Dixie\LaravelModelFuture\Tests\Traits;

use Dixie\LaravelModelFuture\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Dixie\LaravelModelFuture\FuturePlanner;
use Carbon\Carbon;

class HasFutureTest extends TestCase
{
    public function test_it_defines_a_morph_many_relationship_called_futures()
    {
        $user = $this->createUser();

        $this->assertInstanceOf(MorphMany::class, $user->futures());
    }

    public function test_it_makes_the_user_able_to_plan_using_a_future_planner_instance()
    {
        $user = $this->createUser();

        $this->assertInstanceOf(FuturePlanner::class, $user->future());
    }

    public function test_it_can_commit_to_a_state()
    {
        $user = $this->createUser();
        $today = Carbon::now();
        $tomorrow = Carbon::now()->addDay();

        $future1 = $this->createFuturePlanFor($user, $today);
        $future2 = $this->createFuturePlanFor($user, $tomorrow);

        $this->assertTrue(
            $user->future()->see($today)->commit()
        );
        $this->assertEquals($future1->fresh()->committed, Carbon::now());
        $this->assertNull($future2->fresh()->committed);
    }
}

