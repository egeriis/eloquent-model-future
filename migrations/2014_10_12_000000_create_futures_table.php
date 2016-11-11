<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('futures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('createe_user_id')->unsinged()->nullable();
            $table->morphs('futureable');
            $table->json('data');
            $table->timestamp('commit_at');
            $table->timestamp('committed_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['committed_at', 'commit_at'], 'committed_at_and_commit_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('futures');
    }
}
