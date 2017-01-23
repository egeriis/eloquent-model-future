<?php

namespace Dixie\EloquentModelFuture\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Dixie\EloquentModelFuture\Contracts\ModelFuture;
use Dixie\EloquentModelFuture\Models\Future;
use Dixie\EloquentModelFuture\Tests\TestCase;
use Dixie\EloquentModelFuture\FuturePlan;
use Dixie\EloquentModelFuture\Collections\FutureCollection;
use Illuminate\Support\Facades\Auth;
use Dixie\EloquentModelFuture\Tests\Models\User;

class FuturePlannerTest extends TestCase
{
    public function testItCanPlanAFuture()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $user->future()->plan([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ])->at($tomorrow);

        $this->assertInstanceOf(Future::class, $future);
        $this->assertEquals($user->id, $future->futureable_id);
        $this->assertEquals(User::class, $future->futureable_type);
        $this->assertNull($future->committed_at);
        $this->assertEquals($future->commit_at, $tomorrow);
        $this->assertEquals($future->data, [
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ]);
    }

    public function testItAssociatesAuthenticatedUserAsCreator()
    {
        $authUser = $this->createUser();
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        Auth::login($authUser);

        $future = $user->future()->plan([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ])->at($tomorrow);

        $this->assertEquals($authUser->id, $future->createe_user_id);
    }

    public function testItCanAssertAndReturnTrueIfModelHasAnyFuturePlans()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $this->createFuturePlanFor($user, $tomorrow);

        $this->assertTrue($user->future()->hasAnyPlans());
    }

    public function testItCanAssertAndReturnFalseIfModelDoesNotHaveAnyFuturePlans()
    {
        $user = $this->createUser();

        $this->assertFalse($user->future()->hasAnyPlans());
    }

    public function testItCanAssertWhetherItHasAnyFuturePlansForGivenDate()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $this->createFuturePlanFor($user, $tomorrow);

        $hasPlansForTomorrow = $user->future()->hasAnyPlansFor($tomorrow);
        $hasPlansForNextWeek = $user->future()->hasAnyPlansFor($nextWeek);

        $this->assertTrue($hasPlansForTomorrow, 'Expected to have plans for tomorrow');
        $this->assertFalse($hasPlansForNextWeek, 'Expected not to have plans for next week');
    }

    public function testItCanAssertWhetherItHasAnyFuturePlansUntilTheGivenDate()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();
        $nextMonth = Carbon::now()->addMonth();
        $previousMonth = Carbon::now()->subMonth();

        $future = $this->createFuturePlanFor($user, $nextWeek);
        $future = $this->createFuturePlanFor($user, $nextMonth);

        $hasPlansForTomorrow = $user->future()->hasAnyPlansUntil($tomorrow);
        $hasPlansForNextMonth = $user->future()->hasAnyPlansUntil($nextMonth);

        $this->assertFalse($hasPlansForTomorrow);
        $this->assertTrue($hasPlansForNextMonth);
    }

    public function testItCanGetAllFuturePlans()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future = $this->createFuturePlanFor($user, $tomorrow);

        $futurePlans =  $user->future()->getPlans();

        $this->assertInstanceOf(FutureCollection::class, $futurePlans);
        $this->assertCount(2, $futurePlans);
    }

    public function testItCanGetFuturePlansForAGivenDay()
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

    public function testItCanGetAllFuturePlansUntilAGivenDay()
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

    public function testItDoesNotIncludeCommittedFuturePlansInCollections()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $future1 = $this->createFuturePlanFor($user, $tomorrow);
        $future2 = $this->createFuturePlanFor($user, $tomorrow);
        $future3 = $this->createFuturePlanFor($user, $nextWeek);

        $future3->committed_at = Carbon::now();
        $future3->save();

        $futurePlansUntil = $user->future()->getPlansUntil($nextWeek);
        $futurePlansFor = $user->future()->getPlansFor($nextWeek);

        $this->assertCount(2, $futurePlansUntil);
        $this->assertTrue($futurePlansUntil->first()->is($future1));
        $this->assertTrue($futurePlansUntil->last()->is($future2));

        $this->assertEmpty($futurePlansFor);
    }

    public function testItDoesNotIncludeCommittedFuturePlansWhenAssertingExistence()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future->update([
            'committed_at' => Carbon::now(),
        ]);

        $hasAnyFuturePlans = $user->future()->hasAnyPlans();
        $this->assertFalse($hasAnyFuturePlans);
    }

    public function testItDoesNotIncludeCommittedFuturePlansWhenAssertingExistenceOnAGivenDate()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);
        $future->update([
            'committed_at' => Carbon::now(),
        ]);

        $hasPlansForNextWeek = $user->future()->hasAnyPlansFor($tomorrow);
        $this->assertFalse($hasPlansForNextWeek);
    }

    public function testItCanSeeWhatTheModelLooksLikeInTheFuture()
    {
        $today = Carbon::now();
        $user = $this->createUser();

        $this->createFuturePlanFor($user, $today, [
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ]);

        $futureUser =  $user->future()->see($today);
        $user = $user->future()->getPlansFor($today)->result();

        $this->assertInstanceOf(User::class, $futureUser);
        $this->assertTrue($futureUser->is($user));
        $this->assertEquals($futureUser->getAttributes(), $user->getAttributes());

        $this->assertEquals($user->name, 'John Doe');
        $this->assertEquals($user->email, 'jo.do@dixie.io');
        $this->assertEquals($user->bio, 'I am a developer at dixie.io');
    }
}
