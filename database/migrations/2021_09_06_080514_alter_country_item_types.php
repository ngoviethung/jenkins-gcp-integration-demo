<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCountryItemTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('countries', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('country_code');
            $table->string('country_name');
            $table->timestamps();
        });

        Schema::create('topic_country', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('topic_id');
            $table->string('country_id');
        });

        Schema::table('types', function (Blueprint $table) {
            $table->integer('parent_id')->nullable();
        });

        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'TopicCountry'
        ]);

        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('country_code');
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
        Schema::dropIfExists('countries');
        Schema::dropIfExists('topic_country');

        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
