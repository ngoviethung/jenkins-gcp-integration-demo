<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_countries', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->integer('country_id');
        });

        //
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->integer('topic_id')->nullable();
            $table->string('banner')->nullable();
            $table->string('icon')->nullable();
            $table->string('thumb')->nullable();
            $table->string('color')->default('#000000');
            $table->integer('level_unlock')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('vip')->default(0);
            $table->longText('rewards')->nullable();

//            $table->dropColumn('code');
//            $table->dropColumn('number_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_countries');

        //
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('topic_id');
            $table->dropColumn('banner');
            $table->dropColumn('icon');
            $table->dropColumn('thumb');
            $table->dropColumn('color');
            $table->dropColumn('level_unlock');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('vip');
            $table->dropColumn('rewards');
        });
    }
}
