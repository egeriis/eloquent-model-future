<?php

namespace Dixie\EloquentModelFuture\Commands;

use Illuminate\Console\Command;
use Dixie\EloquentModelFuture\Models\Future;
use Carbon\Carbon;

class CommitToFutureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'future:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A command to automatically commit future plans.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::now();
        $futures = Future::with('futureable')
            ->forDate($today)
            ->uncommitted()
            ->get();


        if ($futures->isEmpty()) {
            $this->outputMessage('No future plans for today.');
            return;
        }

        $futures->each(function (Future $future) use ($today) {
            $modelWithFuture = $future->futureable;

            $modelWithFuture->future()
                ->see($today)
                ->commit();
        });

        $this->outputMessage("{$futures->count()} futures updated.");
    }


    /**
     * Write a line to the commandline
     *
     * @return void
     */
    private function outputMessage($message)
    {
        $laravel = $this->laravel ?: false;

        if (! $laravel) {
            return;
        }

        if (! $laravel->runningInConsole()) {
            return;
        }

        $this->info($message);
    }
}
