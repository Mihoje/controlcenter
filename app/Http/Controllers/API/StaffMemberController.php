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
            $s->user->name = $s->user->name; // Eager load
            $s->user = $s->user->only(['name','id','rating_short']);
            $filtered->push($s->only(['position','title','user']));
        });

        return response()->json($filtered);
    }
}
