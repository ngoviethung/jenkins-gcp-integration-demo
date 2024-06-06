<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScoreColumnToItemStyleTalbe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('item_style')) {
            if (!Schema::hasColumn('item_style', 'score')) {
                Schema::table('item_style', function (Blueprint $table) {
                    $table->integer('score')->after('style_id')->default(0);
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
        Schema::table('item_style', function (Blueprint $table) {
            //
        });
    }
}
