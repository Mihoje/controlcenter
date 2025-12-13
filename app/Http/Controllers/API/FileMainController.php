<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Request;
use App\Models\FileMain;

class FileMainController extends Controller
{
    
    public function getFiles(){
        $policy = FileMain::where('type', 'Policy')->orderBy('name', 'ASC')->get(DB::Raw("SUBSTRING_INDEX(SUBSTRING_INDEX(name, '.', 1), '.', -1) AS 'name', path, updated_at"));
        $loa = FileMain::where('type', 'LOA')->orderBy('name', 'ASC')->get(DB::Raw("SUBSTRING_INDEX(SUBSTRING_INDEX(name, '.', 1), '.', -1) AS 'name', path, updated_at"));
        $training = FileMain::where('type', 'Training document')->orderBy('name', 'ASC')->get(DB::Raw("SUBSTRING_INDEX(SUBSTRING_INDEX(name, '.', 1), '.', -1) AS 'name', path, updated_at"));

        $files = collect()->merge($policy)->merge($loa)->merge($training);

        foreach ($files as $file) {
            $file->url = asset('storage' . $file->path);
        }

        return response()->json([ 'success' => true, 'root_url' => Request::root(), 'policy' => $policy, 'loa' =>$loa, 'training' => $training ]);
    }

}