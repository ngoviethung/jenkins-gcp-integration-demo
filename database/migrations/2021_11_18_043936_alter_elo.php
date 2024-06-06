<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterElo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('elo_votes', function (Blueprint $table) {
            $table->timestamps();
            $table->tinyInteger('status')->default(0);
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
        Schema::table('elo_votes', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');

            $table->dropColumn('status');
        });
    }
}
