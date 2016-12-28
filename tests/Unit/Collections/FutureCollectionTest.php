<?php

namespace Dixie\EloquentModelFuture\Tests\Unit\Collections;

use Dixie\EloquentModelFuture\Tests\TestCase;
use Carbon\Carbon;
use Dixie\EloquentModelFuture\Collections\FutureCollection;
use Dixie\EloquentModelFuture\Tests\User;
use Dixie\EloquentModelFuture\Contracts\ModelFuture;

class FutureCollectionTest extends TestCase
{
    public function testItIsTheDefaultCollectionWhenGettingFutures()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $this->createFuturePlanFor($user, $tomorrow);

        $futures = $user->futures;

        $this->assertInstanceOf(FutureCollection::class, $futures);
        $this->assertCount(1, $futures);
    }

    public function testItCanShowHowTheModelWasOriginally()
    {
        $user = $this->createUser([
            'name' => 'Jakob Steinn',
            'email' => 'ja.st@dixie.io',
            'bio' => 'Developer@dixie',
        ]);
        $tomorrow = Carbon::now()->addDay();
        $this->createFuturePlanFor($user, $tomorrow);

        $userBeforeFutures = $user->futures->original();

        $this->assertCount(1, $userBeforeFutures);
        $userBeforeFutures->each(function ($userBefore) use ($user) {
            $this->assertTrue($userBefore->is($user));
            $this->assertInstanceOf(ModelFuture::class, $userBefore);
        });
    }

    public function testItCanShowHowTheModelWillLookAsAResultOfFuturePlans()
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
            $user->futures->result()->getAttributes(),
            array_merge($user->getAttributes(), [
                'name' => $planForNextWeek->data['name'],
                'email' => $planForNextWeek->data['email'],
                'bio' => $user->bio,
                'birthday' => $planForTomorrow->data['birthday'],
            ])
        );
    }

    public function testItCanShowTheDiffBetweenBeforeAndAfter()
    {
        $user = $this->createUser();
        $tomorrow = Carbon::now()->addDay();

        $future = $this->createFuturePlanFor($user, $tomorrow);

        $this->assertEquals($user->futures->resultDiff()->toArray(), [
            [
                'before' => json_encode([
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
                'after' => json_encode([
                    'name' => $future->data['name'],
                    'email' => $future->data['email'],
                ]),
                'commit_at' => $tomorrow,
            ],
        ]);
    }
}
