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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->integer('status');
        });

        $this->moveData();

        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('read_only');
        });
    }

    private function moveData(){
        DB::statement("UPDATE `api_keys` SET `status`=`read_only`");
    }
    
    private function moveDataBack(){
        DB::statement("UPDATE `api_keys` SET `read_only`=IF(`status`=0, 0, 1)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->boolean('read_only');
        });

        $this->moveDataBack();

        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};