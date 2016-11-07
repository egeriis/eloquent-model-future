<?php

namespace Dixie\LaravelModelFuture\Tests\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Dixie\LaravelModelFuture\Contracts\ModelFuture;
use Dixie\LaravelModelFuture\Models\Future;
use Dixie\LaravelModelFuture\Tests\TestCase;
use Dixie\LaravelModelFuture\FuturePlan;
use Dixie\LaravelModelFuture\Tests\User; use Dixie\LaravelModelFuture\Collections\FutureCollection; 
class FutureTest extends TestCase
{

    public function test_it_can_plan_a_future()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $user->future()->plan([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ])->for($tomorrow);

        $this->assertInstanceOf(Future::class, $future);
        $this->assertEquals($user->id, $future->futureable_id);
        $this->assertEquals(User::class, $future->futureable_type);
        $this->assertNull($future->committed);
        $this->assertEquals($future->commit_at, $tomorrow);
        $this->assertEquals($future->data, [
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ]);
    }

    public function test_it_can_assert_if_any_future_plans_have_been_made()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $future = $this->createFuturePlanFor($user, $tomorrow);

        $hasPlansForTomorrow = $user->future()->anyPlansFor($tomorrow);
        $hasPlansForNextWeek = $user->future()->anyPlansFor($nextWeek);

        $this->assertTrue($hasPlansForTomorrow);
        $this->assertFalse($hasPlansForNextWeek);
    }

    public function test_it_can_get_future_plans_for_a_specific_day()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future = $this->createFuturePlanFor($user, $tomorrow, [
            'bio' => 'I am one week ahead!',
        ]);

        $futurePlans =  $user->future()->getPlansFor($tomorrow);

        $this->assertInstanceOf(FutureCollection::class, $futurePlans);
        $this->assertCount(2, $futurePlans);
    }

    public function test_it_can_get_all_future_plans_untill_a_day()
    {
        $user = $this->createUser([
            'bio' => 'I am a developer at dixie.io',
        ]);
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();
        $nextMonth = Carbon::now()->addMonth();

        $future = $this->createFuturePlanFor($user, $tomorrow, [
            'name' => 'John Doe',
        ], true);
        $future = $this->createFuturePlanFor($user, $nextWeek, [
            'email' => 'jo.do@dixie.io',
        ], true);
        $future = $this->createFuturePlanFor($user, $nextMonth, [
            'bio' => 'I never get to play... *sadface*',
        ], true);

        $futurePlans =  $user->future()->getPlansUntill($nextWeek);

        $this->assertInstanceOf(FutureCollection::class, $futurePlans);
        $this->assertCount(2, $futurePlans);
        $this->assertEquals(
            $futurePlans->after()->toArray(), 
            array_merge($user->getAttributes(), [
                'name' => 'John Doe',
                'email' => 'jo.do@dixie.io',
                'bio' => 'I am a developer at dixie.io',
            ])
        );

    }
}
