<?php

namespace App\Http\Controllers;

use App\Models\AirportEndorsement;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ApiController extends Controller
{
    public function apendorsements_show(){

        $results = DB::select( "SELECT sub.user_id, sub.rating, sub.is_visiting, sub.atc_active, CONCAT(SUBSTRING(sub.first_name, 1, 1), '.', SUBSTRING(sub.last_name, 1, 1), '.') AS 'name', sub.airport, IF(MAX(strength)=0, 'DEL', IF(MAX(strength)=1, 'GND', IF(MAX(strength)=2, 'TWR', 'APP'))) 'highest_position' FROM (SELECT user_id, atc_active, users.rating_short AS 'rating', IF(STRCMP(users.subdivision, 'ADRIA')=0, 0, 1) AS 'is_visiting', first_name, last_name, airport, IF(STRCMP(ap_endorsements.position, 'DEL') = 0, 0, IF(STRCMP(ap_endorsements.position, 'GND')=0, 1, IF(STRCMP(ap_endorsements.position, 'TWR')=0, 2, 3))) AS strength FROM user_apendorsement JOIN ap_endorsements ON user_apendorsement.endorsement_id=ap_endorsements.id JOIN users ON user_apendorsement.user_id=users.id) sub GROUP BY user_id, airport ORDER BY sub.strength DESC, sub.airport ASC;");

        $output = [];

        foreach ($results as $endorsement) {
            $uid = $endorsement->user_id;
            if(array_key_exists($uid, $output)){

                array_push($output[$uid]['endorsements'], [
                    'airport' => $endorsement->airport,
                    'highest_position' => $endorsement->highest_position
                ]);

            } else {

                $output[$uid] = [
                    'name' => $endorsement->name,
                    'rating' => $endorsement->rating,
                    'atc_active' => $endorsement->atc_active,
                    'is_visiting' => $endorsement->is_visiting,
                    'endorsements' => [[
                        'airport' => $endorsement->airport,
                        'highest_position' => $endorsement->highest_position
                    ]]
                ];

            }
        }

        return response()->json(['success'=>false,'users'=>$output]);
    }

}