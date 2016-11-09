<?php

namespace Dixie\LaravelModelFuture\Tests\Commands;

use Dixie\LaravelModelFuture\Tests\TestCase;
use Dixie\LaravelModelFuture\Commands\CommitToFutureCommand;
use Carbon\Carbon;
use Dixie\LaravelModelFuture\Models\Future;

class CommitToFutureCommandTest extends TestCase
{
    public function test_it_commits_future_plans_for_today_when_run()
    {
        $nextMonth = Carbon::now()->addMonth();
        $today = Carbon::now();
        $command = new CommitToFutureCommand;
        $jakob = $this->createUser();
        $john = $this->createUser([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
            'bio' => 'I am not human.',
            'birthday' => Carbon::now()->subYear()->subMonth(),
        ]);

        $jakobsFuture = $this->createFuturePlanFor($jakob, $today);

        $johnsFuture = $this->createFuturePlanFor($john, $nextMonth);

        $command->handle();

        $allFutures = Future::get(['id', 'committed']);
        $committedFutures = Future::committed()->get();
        $uncommittedFutures = Future::uncommitted()->get();

        $this->assertCount(1, $uncommittedFutures);
        $this->assertTrue(
            $uncommittedFutures->first()->is($johnsFuture)
        );

        $this->assertCount(1, $committedFutures);
        $this->assertTrue(
            $committedFutures->first()->is($jakobsFuture)
        );
    }
}
