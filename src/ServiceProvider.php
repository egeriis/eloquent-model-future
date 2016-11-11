<?php

namespace Dixie\LaravelModelFuture;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Dixie\LaravelModelFuture\Commands\CommitToFutureCommand;


class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        dump(__DIR__);
        $this->loadMigrationsFrom(__DIR__.'/../migrations');

        $this->commands(CommitToFutureCommand::class);
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
