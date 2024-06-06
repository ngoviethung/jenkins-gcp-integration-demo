<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFactorColumnToTaskTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('task_type')) {
            if (!Schema::hasColumn('task_type', 'factor')) {
                Schema::table('task_type', function (Blueprint $table) {
                    $table->integer('factor')->after('type_id')->default(0);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_type', function (Blueprint $table) {
            //
        });
    }
}
