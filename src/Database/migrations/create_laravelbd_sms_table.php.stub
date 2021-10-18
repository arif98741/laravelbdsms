<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelbdSmsTable extends Migration
{
    /**
     * Run the Migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * table prefix @lbs_
         * @table lbs_log
         */
        Schema::create('lbs_log', function (Blueprint $table) {
            $table->increments('id');
            $table->text('provider');
            $table->text('request_json');
            $table->text('response_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the Migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lbs_log');
    }
}
