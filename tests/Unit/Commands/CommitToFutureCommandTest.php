<?php

namespace Dixie\EloquentModelFuture\Tests\Unit\Commands;

use Dixie\EloquentModelFuture\Tests\TestCase;
use Dixie\EloquentModelFuture\Commands\CommitToFutureCommand;
use Carbon\Carbon;
use Dixie\EloquentModelFuture\Models\Future;

class CommitToFutureCommandTest extends TestCase
{
    public function testItCommitsFuturePlansForTodayWhenRun()
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
