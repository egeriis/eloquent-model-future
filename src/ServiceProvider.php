<?php

namespace Dixie\EloquentModelFuture;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Dixie\EloquentModelFuture\Commands\CommitToFutureCommand;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        if ($this->app->runningInConsole()) {
            $this->commands(CommitToFutureCommand::class);
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }
}
