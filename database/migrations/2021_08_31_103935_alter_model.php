<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('models', function (Blueprint $e) {
            $e->longText('default_items')->nullable();
            $e->integer('sort_order')->default(0);
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
        Schema::table('models', function (Blueprint $e) {
            $e->dropColumn('default_items');
            $e->dropColumn('sort_order');
        });
    }
}
