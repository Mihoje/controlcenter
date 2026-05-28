<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\StaffMember;
use App\Models\User;

class StaffMemberController extends Controller
{
    public function get(){

        $staff = StaffMember::orderBy('position', 'asc')->with('user')->get();

        $filtered = collect();

        $staff->each(function($s) use ($filtered){
            $s->user->append('public_name'); // Eager load
            $s->user = $s->user->only(['public_name','id','rating_short']);
            $filtered->push($s->only(['position','title','callsign','user']));
        });

        return response()->json($filtered);
    }
}
