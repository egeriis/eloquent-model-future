<?php

namespace Dixie\EloquentModelFuture\Tests;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Dixie\EloquentModelFuture\Tests\Models\User;
use Dixie\EloquentModelFuture\Contracts\ModelFuture;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setupDatabase($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../migrations'),
        ]);

        // Setup dummy users
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('name');
            $table->text('bio')->nullable();
            $table->timestamp('birthday')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function createFuturePlanFor(ModelFuture $model, $date, array $data = [])
    {
        $attributes = array_merge($data, [
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ]);

        return $model->future()->plan($attributes)->at($date);
    }

    protected function createUser(array $data = [])
    {
        $attributes = array_merge($data, [
            'name' => 'Jakob Steinn',
            'email' => 'ja.st@dixie.io',
            'bio' => 'I am a developer at dixie.io',
            'birthday' => Carbon::now()->subYear(),
        ]);

        return User::create($attributes);
    }
}
