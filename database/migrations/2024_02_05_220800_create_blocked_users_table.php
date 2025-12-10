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
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(\App\Models\User::class, 'blocked_user_id')->constrained('users');
            $table->foreignIdFor(\App\Models\User::class, 'issued_by')->constrained('users');
            $table->string('reason');

            $table->unique('blocked_user_id');

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
        Schema::dropIfExists('blocked_users');
    }
};