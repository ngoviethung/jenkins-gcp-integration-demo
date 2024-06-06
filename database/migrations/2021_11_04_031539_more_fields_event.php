<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoreFieldsEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('events', function (Blueprint $table) {
            $table->integer('unlock_by_gem')->nullable();
            $table->integer('unlock_by_ticket')->nullable();
            $table->integer('entry_fee_gem')->nullable();
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
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('unlock_by_gem');
            $table->dropColumn('unlock_by_ticket');
            $table->dropColumn('entry_fee_gem');
        });
    }
}
