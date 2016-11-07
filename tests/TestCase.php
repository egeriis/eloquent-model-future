<?php

namespace Dixie\LaravelModelFuture\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit_Framework_TestCase;
use Dixie\LaravelModelFuture\Contracts\ModelFuture;
use Carbon\Carbon;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Eloquent::unguard();
        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();

        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('email');
            $table->string('name');
            $table->text('bio')->nullable();
            $table->timestamp('birthday')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('futures', function ($table) {
            $table->increments('id');
            $table->integer('futureable_id');
            $table->string('futureable_type');
            $table->timestamp('commit_at');
            $table->json('data');
            $table->timestamp('committed')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function tearDown()
    {
        $this->schema()->drop('users');
        $this->schema()->drop('futures');
    }

    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    } 
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    protected function createFuturePlanFor(ModelFuture $model, $date, array $data = [], $shouldOverride = false)
    {
        $attributes = array_merge($data, [
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ]);

        if($shouldOverride) {
            $attributes = $data;
        }

        return $model->future()->plan($attributes)->for($date);
    }

    protected function createUser(array $data = [], $shouldOverride = false)
    {
        $attributes = array_merge($data, [
            'name' => 'Jakob Steinn',
            'email' => 'ja.st@dixie.io',
            'bio' => 'I am a developer at dixie.io',
            'birthday' => Carbon::now()->subYear(),
        ]);

        if($shouldOverride) {
            $attributes = $data;
        }

        return User::create($attributes);
    }
}

class User extends Eloquent implements ModelFuture
{
    use \Dixie\LaravelModelFuture\Traits\HasFuture;
}


