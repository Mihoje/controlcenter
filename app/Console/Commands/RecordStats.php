<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use App\Models\User;

class RecordStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'record:member:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record and save member statistics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $time = Carbon::now();

        $stats = [
            'users_in_cc' => $this->getUsersInCC(),
            'users_in_subdivision' => $this->getUsersInSubdivision(),
            'visiting_controllers' => $this->getVisitingControllers(),
            'active_atc' => $this->getActiveAtc()
        ];

        $insert = [];

        $keys = DB::table('member_statistics')->where('created_at', '>', Carbon::now()->subHours(24))->select('key')->get();

        foreach ($stats as $key => $value) {
            if($keys->where('key', $key)->count()) continue;

            array_push($insert, ['key'=>$key,'value'=>$value,'created_at'=>$time,'updated_at'=>$time]);            
        }

        DB::table('member_statistics')->insert($insert);

        $this->info('All statistics saved.');
    }

    private function getUsersInCC() : int {
        return User::count();
    }

    private function getUsersInSubdivision() {
        $url = sprintf('https://api.vatsim.net/v2/orgs/%s/%s', config('app.mode'), config('app.owner_code'));
        $headers = [
            'X-API-Key' => config('vatsim.core_api_token'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $count = -1;

        $response = Http::withHeaders($headers)->get(sprintf('%s?include_inactive=1&limit=%s', $url, 1));

        if (!$response->successful()) {
            return false;
        }

        $jsonResponse = $response->json();
        
        return $jsonResponse['count'];
    }

    private function getVisitingControllers() : int {
        return User::whereHas('endorsements', function($q){
            $q->where('type', 'VISITING')->where('expired', 0)->where('revoked', 0);
        })->count();
    }

    private function getActiveAtc() : int {
        return User::getActiveAtcMembers()->count();
    }


}
