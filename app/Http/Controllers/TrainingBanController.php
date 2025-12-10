<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TrainingBan;
use Auth;
use Carbon\Carbon;

class TrainingBanController extends Controller
{
    public function show($inactive){
        $this->authorize('view', TrainingBan::class);

        $bans = TrainingBan::where('expires_on', $inactive ? '<=' : '>', Carbon::now()->toDateString())->orderBy('created_at', 'DESC')->get();

        return view('training_bans.show', compact('bans', 'inactive'));
    }

    public function revoke($id){
        $ban = TrainingBan::find($id);

        if(!$ban){
            return redirect()->back()->withErrors(['Training ban doesn\'t exist']);
        }
        
        $this->authorize('revoke', $ban);

        $ban->expires_on = Carbon::now()->toDateString();
        $ban->save();

        return redirect()->back()->with('success', 'Training ban revoked');
    }

    public function create(User $prefillUser){
        $this->authorize('create', TrainingBan::class);

        $students = User::all();

        return view('training_bans.create', compact('prefillUser','students'));
    }

    public function store(Request $r){
        $r->validate([
           'user_id'  => 'required|exists:App\Models\User,id',
           'expires' => 'required|date_format:d/m/Y',
           'reason' => 'required|string'
        ]);

        $user = User::find($r->user_id);

        $ban = new TrainingBan();

        $ban->user()->associate($user);
        $ban->expires_on = Carbon::createFromIsoFormat('DD/MM/YYYY', $r->expires)->toDateString();
        $ban->reason = $r->reason;
        $ban->issuer()->associate(Auth::user());

        $ban->save();

        return redirect()->back()->with('success', 'Training ban issued successfully');
    }
}
