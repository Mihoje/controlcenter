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
        Schema::create('training_bans', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->constrained('users');
            $table->date('expires_on');
            $table->string('reason');
            $table->foreignIdFor(\App\Models\User::class, 'issued_by')->constrained('users');
            
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
        Schema::dropIfExists('training_bans');
    }
};