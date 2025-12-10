<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Training;
use App\Models\User;
use App\Models\AtcActivity;

class StatsController extends Controller
{
    
    public function allStats(){

        $completedTrainings = Cache::remember('completedTrainingsNo', 3600, function() { 
            return Training::where('status', -1)->where('closed_at', '>=', Carbon::now()->subYears(1))->count();
        });

        $activeTrainings = Cache::remember('activeTrainingsNo', 3600, function(){
            return Training::where('status', 2)->orWhere('status', 1)->count();
        });

        $queue = Cache::remember('trainingQueueLength', 3600, function(){
            return Training::where('status', 'IN', [0, 1])->count();
        });

        $totalHours = Cache::remember('totalHoursInLast12Months', 3600, function () {

            $users = User::where('subdivision', 'ADRIA')->get();

            $output = 0;

            foreach($users as $user){
                $output += ($user->atcActivity->count()) ? $user->atcActivity->sum('hours') : 0;
            }

            return $output;
        });
        
        $data = [
            'completed'=>$completedTrainings,
            'active'=>$activeTrainings,
            'queue'=>$queue,
            'hours'=>$totalHours
        ];


        return response()->json($data);
    }

}