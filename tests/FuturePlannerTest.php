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
class FuturePlannerTest extends TestCase
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

    public function test_it_can_assert_and_return_true_if_model_has_any_future_plans()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);

        $this->assertTrue($user->future()->hasAnyPlans());
    }

    public function test_it_can_assert_and_return_false_if_model_does_not_have_any_future_plans()
    {
        $user = $this->createUser();

        $this->assertFalse($user->future()->hasAnyPlans());
    }

    public function test_it_can_assert_if_any_future_plans_have_been_made()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $future = $this->createFuturePlanFor($user, $tomorrow);

        $hasPlansForTomorrow = $user->future()->hasAnyPlansFor($tomorrow);
        $hasPlansForNextWeek = $user->future()->hasAnyPlansFor($nextWeek);

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

        $futurePlans =  $user->future()->getPlansUntil($nextWeek);

        $this->assertInstanceOf(FutureCollection::class, $futurePlans);
        $this->assertCount(2, $futurePlans);
    }

    public function test_it_does_not_include_committed_plans_in_collections()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $future1 = $this->createFuturePlanFor($user, $tomorrow);
        $future2 = $this->createFuturePlanFor($user, $tomorrow);
        $future3 = $this->createFuturePlanFor($user, $nextWeek);

        $future3->committed = Carbon::now();
        $future3->save();

        $futurePlansUntil = $user->future()->getPlansUntil($nextWeek);
        $futurePlansFor = $user->future()->getPlansFor($nextWeek);

        $this->assertCount(2, $futurePlansUntil);
        $this->assertTrue($futurePlansUntil->first()->is($future1));
        $this->assertTrue($futurePlansUntil->last()->is($future2));

        $this->assertEmpty($futurePlansFor);
    }

    public function test_it_does_not_include_committed_future_plans_when_asserting_existence()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future->update([
            'committed' => Carbon::now(),
        ]);

        $hasAnyFuturePlans = $user->future()->hasAnyPlans();
        $this->assertFalse($hasAnyFuturePlans);
    }

    public function test_it_does_not_include_committed_future_plans_when_asserting_existence_on_given_date()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future->update([
            'committed' => Carbon::now(),
        ]);

        $hasFuturePlansForNextWeek = $user->future()->hasAnyPlansFor($tomorrow);
        $this->assertFalse($hasFuturePlansForNextWeek);

    }
}
