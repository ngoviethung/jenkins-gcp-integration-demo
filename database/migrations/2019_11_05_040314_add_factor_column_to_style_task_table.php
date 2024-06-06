<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFactorColumnToStyleTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('style_task')) {
            if (!Schema::hasColumn('style_task', 'factor')) {
                Schema::table('style_task', function (Blueprint $table) {
                    $table->integer('factor')->after('style_id')->default(0);
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
        Schema::table('style_table', function (Blueprint $table) {
            //
        });
    }
}
