<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StaffMember;
use App\Models\User;

class StaffMemberController extends Controller
{
    //

    public function show(){
        $this->authorize('viewAny', StaffMember::class);

        $staff = StaffMember::orderBy('position', 'asc')->get();
        $users = User::get(['id','first_name','last_name']);

        return view('staff_members.show', compact('staff', 'users'));
    }

    public function store(Request $r){
        $this->authorize('create', StaffMember::class);

        $r->validate([
            'user' => 'required|integer|exists:users,id',
            'title' => 'string|required',
            'callsign' => 'required|string',
        ]);

        $member = new StaffMember();

        $member->user_id = $r->user;
        $member->title = $r->title;
        $member->callsign = $r->callsign;

        $member->save();

        return redirect()->back()->with('success', 'Successfully added staff member');
    }

    public function order(Request $r){
        $this->authorize('update', StaffMember::class);

        foreach($r->collect() as $key => $value){
            if($key == '_token') continue;

            $member = StaffMember::find($key);

            if(!$member){
                return response()->json(['success'=>false,'reason'=>'Invalid member id']);
            } else if(!is_numeric($value) ){
                return response()->json(['success'=>false,'reason'=>'Invalid type ' . gettype($value)]);
            }

            $member->position = $value;
            $member->save();
        }

        return response()->json(['success'=>true]);
    }

    public function destroy($id){
        $member = StaffMember::find($id);

        if(!$member){
            return redirect()->back()->withErrors(['Staff member doesn\'t exist']);
        }

        $this->authorize('delete', $member);

        $member->delete();

        return redirect()->back()->with('success', 'Successfully removed staff member');
    }
}
