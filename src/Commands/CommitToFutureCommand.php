<?php

namespace Dixie\LaravelModelFuture\Commands;

use Illuminate\Console\Command;
use Dixie\LaravelModelFuture\Models\Future;
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

        $futures->each(function(Future $future) use ($today) {
            $modelWithFuture = $future->futureable;

            $modelWithFuture->future()
                ->see($today)
                ->commit();
        });
    }
}
