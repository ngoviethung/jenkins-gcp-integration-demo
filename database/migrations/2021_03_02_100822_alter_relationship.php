<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('items', function (Blueprint $table) {
            $table->integer('group_level_item_id')->nullable();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('group_level_task_id')->nullable();
        });

        Schema::table('types', function (Blueprint $table) {
            $table->integer('group_level_type_id')->nullable();
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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('group_level_item_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('group_level_task_id');
        });

        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('group_level_type_id');
        });
    }
}
