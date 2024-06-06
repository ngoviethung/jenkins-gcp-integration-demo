<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('items', function (Blueprint $e) {
            $e->double('elo_score', 10, 2)->default(1000);
        });

        Schema::create('elo_votes', function (Blueprint $e) {
            $e->integer('id')->autoIncrement();
            $e->integer('user_id');
            $e->integer('topic_id');
            $e->integer('type_id');
            $e->integer('win_item_id');
            $e->integer('lost_item_id');
            $e->double('win_score', 10, 2);
            $e->double('lost_score', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('items', function (Blueprint $e) {
            $e->dropColumn('elo_score');
        });

        Schema::dropIfExists('elo_votes');
    }
}
