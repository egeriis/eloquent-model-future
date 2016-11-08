<?php

namespace Dixie\LaravelModelFuture\Tests\Collections;

use Dixie\LaravelModelFuture\Tests\TestCase;
use Carbon\Carbon;
use Dixie\LaravelModelFuture\Collections\FutureCollection;
use Dixie\LaravelModelFuture\Tests\User;

class FutureCollectionTest extends TestCase
{
    public function test_it_is_the_default_collection_when_getting_futures()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $this->createFuturePlanFor($user, $tomorrow);

        $futures = $user->futures;

        $this->assertInstanceOf(FutureCollection::class, $futures);
        $this->assertCount(1, $futures);
    }

    public function test_it_can_show_how_the_model_was_before()
    {
        $user = $this->createUser([
            'name' => 'Jakob Steinn',
            'email' => 'ja.st@dixie.io',
            'bio' => 'Developer@dixie',
        ]);
        $tomorrow = Carbon::now()->addDay();
        $this->createFuturePlanFor($user, $tomorrow);

        $userBeforeFutures = $user->futures->before();

        $this->assertCount(1, $userBeforeFutures);
        $userBeforeFutures->each(function($userBefore) use ($user) {
            $this->assertTrue($userBefore->is($user));
            $this->assertInstanceOf(User::class, $userBefore);
        });
    }

    public function test_it_can_show_how_the_model_will_look_after()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();

        $planForTomorrow = $this->createFuturePlanFor($user, $tomorrow, [
            'name' => 'John Doe',
            'birthday' => Carbon::now()->subYear(),
        ], true);
        $planForNextWeek = $this->createFuturePlanFor($user, $nextWeek, [
            'name' => 'John Foo',
            'email' => 'jo.fo@dixie.io',
        ], true);

        $this->assertEquals(
            $user->futures->after()->getAttributes(),
            array_merge($user->getAttributes(), [
                'name' => $planForNextWeek->data['name'],
                'email' => $planForNextWeek->data['email'],
                'bio' => $user->bio,
                'birthday' => $planForTomorrow->data['birthday'],
            ])
        );
    }

    public function test_it_can_show_the_diff_between_before_and_after()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);

        $this->assertEquals($user->futures->resultDiff()->toArray(), [
            [
                'name' => [
                    'before' => $user->name,
                    'after' => $future->data['name'],
                ],
                'email' => [
                    'before' => $user->email,
                    'after' => $future->data['email'],
                ],
                'commit_at' => $tomorrow,
            ],
        ]);
    }
}
