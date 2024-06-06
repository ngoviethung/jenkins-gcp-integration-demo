<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeItemRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('topic_item_rlt', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('item_id');
            $table->integer('topic_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->integer('type_id')->nullable();
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
    }
}
