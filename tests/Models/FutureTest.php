<?php

namespace Dixie\LaravelModelFuture\Tests\Models;

use Dixie\LaravelModelFuture\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Dixie\LaravelModelFuture\Collections\FutureCollection;
use Dixie\LaravelModelFuture\Models\Future;

class FutureTest extends TestCase
{

    protected $future;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->future = $this->createFuturePlanFor($this->user, Carbon::now());
    }

    public function test_it_casts_its_data_to_array()
    {
        $this->assertInternalType('array', $this->future->data);
    }

    public function test_it_casts_its_dates_to_carbon_instances()
    {
        $this->assertEquals($this->future->getDates(), [
            'commit_at',
            'committed',
            'deleted_at',
            'created_at',
            'updated_at',
        ]);

        // Setup dates so that they are all filled
        //
        // committed date
        $this->future->committed = '1993-12-10';
        $this->future->save();

        // deleted_at date
        $this->future->delete();

        $this->assertInstanceOf(Carbon::class, $this->future->commit_at);
        $this->assertInstanceOf(Carbon::class, $this->future->committed);
        $this->assertInstanceOf(Carbon::class, $this->future->created_at);
        $this->assertInstanceOf(Carbon::class, $this->future->updated_at);
        $this->assertInstanceOf(Carbon::class, $this->future->deleted_at);
    }

    public function test_it_has_a_morph_to_relationship_called_futureable()
    {
        $this->assertInstanceOf(MorphTo::class, $this->future->futureable());
    }

    public function test_it_returns_a_custom_eloquent_collection()
    {
        $this->assertInstanceOf(FutureCollection::class, Future::all());
    }
}
