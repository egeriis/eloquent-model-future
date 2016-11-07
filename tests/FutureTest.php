<?php

namespace Dixie\LaravelModelFuture\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Dixie\LaravelModelFuture\Contracts\ModelFuture;


class FutureTest extends TestCase
{

    public function test_it_can_plan_a_future()
    {
        $tomorrow = Carbon::now()->addDay();
        $user = User::create([
            'name' => 'Jakob Steinn',
            'email' => 'ja.st@dixie.io'
        ]);

        $future = $user->future()->plan([
            'name' => 'John Doe',
            'email' => 'jo.do@dixie.io',
        ])->for($tomorrow);

        $this->assertEquals($user->id, $future->futureable_id);
        $this->assertEquals(User::class, $future->futureable_type);
        $this->assertNull($future->committed);
        $this->assertEquals($future->commit_at, $tomorrow);
    }
}

class User extends Eloquent implements ModelFuture
{
    use \Dixie\LaravelModelFuture\Traits\HasFuture;
}

