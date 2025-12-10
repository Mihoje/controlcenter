<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\Training;
use App\Models\User;
use App\Models\AtcActivity;

class TestAPIController extends Controller
{
    
    public function testFunction(Request $r){

        return response()->json(['success'=>true,'data'=>$r->collect()]);
    }

}