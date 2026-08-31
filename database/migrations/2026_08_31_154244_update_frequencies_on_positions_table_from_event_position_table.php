<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EventPosition;
use App\Models\Position;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $positions = Position::all();

        $positions->each(function ($pos) {
            $epos = EventPosition::where('code', $pos->callsign)->first();
            if($epos){
                $pos->frequency = $epos->frequency;
                $pos->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
