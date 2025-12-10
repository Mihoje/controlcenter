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
        Schema::create('event_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('callsign');
            $table->string('frequency')->nullable();
            $table->integer('required_rating')->unsigned()->nullable();

            $table->foreign('required_rating')->references('id')->on('ratings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_positions');
    }
};