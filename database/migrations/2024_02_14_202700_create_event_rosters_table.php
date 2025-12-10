<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_rosters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->unsigned();
            $table->integer('event_position_id')->unsigned()->nullable();
            $table->dateTime('from')->nullable();
            $table->dateTime('to')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('event_position_id')->references('id')->on('event_positions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_rosters');
    }
};