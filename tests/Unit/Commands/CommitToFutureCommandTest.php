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
        $prevMonth = Carbon::now()->subMonth();
        $today = Carbon::now();
        $nextMonth = Carbon::now()->addMonth();

        $command = new CommitToFutureCommand;
        $jakob = $this->createUser();
        $john = $this->createUser([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
            'bio' => 'I am not human.',
            'birthday' => Carbon::now()->subYear()->subMonth(),
        ]);
        $johnny = $this->createUser([
            'name' => 'Johnny Reimer',
            'email' => 'jo.re@dixie.io',
            'bio' => 'Guitar',
            'birthday' => new Carbon('1953-04-01'),
        ]);

        $johnnysFuture = $this->createFuturePlanFor($johnny, $prevMonth);
        $jakobsFuture = $this->createFuturePlanFor($jakob, $today);
        $johnsFuture = $this->createFuturePlanFor($john, $nextMonth);

        $command->handle();

        $committedFutures = Future::committed()->get();
        $uncommittedFutures = Future::uncommitted()->get();

        $this->assertCount(1, $uncommittedFutures);
        $this->assertTrue(
            $uncommittedFutures->contains($johnsFuture)
        );

        $this->assertCount(2, $committedFutures);
        $this->assertTrue($committedFutures->contains($jakobsFuture));
        $this->assertTrue($committedFutures->contains($johnnysFuture));
    }
}
