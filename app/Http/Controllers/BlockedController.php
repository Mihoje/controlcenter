<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\BlockedUser;
use Auth;
use Carbon\Carbon;

class BlockedController extends Controller
{
    public function show(){

        if(!Auth::user()->hasGlobalRole('admin')){
            throw new HttpException(401);
        }

        $blockedusers = BlockedUser::all();
        $users = User::all();

        return view('admin.blocked', compact('blockedusers', 'users'));
    }

    public function store(Request $request){
        if(!Auth::user()->hasGlobalRole('admin')){
            throw new HttpException(401);
        }
        $validatedData = $request->validate([
            'user' => 'required|integer|unique:blocked_users,blocked_user_id',
            'reason'=>'required|string'
        ]);

        $user = User::find($request->user);

        if(!$user){
            return redirect()->route('blocked.show')->withErrors('User does not exist');
        }

        if(Auth::user()->is($user)){
            return redirect()->route('blocked.show')->withErrors('Don\'t block yourself, silly!');
        }

        $b = new BlockedUser();

        $b->user()->associate($user);
        $b->issuer()->associate(Auth::user());
        $b->reason = $request->reason;

        $b->save();

        $user->update(['token_expires'=>(Carbon::now())->valueOf(),'access_token'=>null,'refresh_token'=>null]);

        return redirect()->route('blocked.show')->with('success', 'User has been blocked.');
    }

    public function delete($id){
        if(!Auth::user()->hasGlobalRole('admin')){
            throw new HttpException(401);
        }

        $b = BlockedUser::find($id);

        if(!$b){
            return redirect()->route('blocked.show')->withErrors('Block id doens\'t exist');
        }
        $b->delete();

        return redirect()->route('blocked.show')->with('success', 'User successfully unblocked');
    }
}
