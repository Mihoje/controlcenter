<?php

namespace App\Http\Controllers;

use App\Models\EventAvailability;
use App\Models\EventPosition;
use App\Models\EventRoster;
use App\Models\EventRosterMentor;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function create(){
        if(!Auth::user()->hasPermission('events.manage')){
            return redirect('/dashboard');
        }

        return view('event.create');
    }

    public function delete($id){
        if(!Auth::user()->hasPermission('events.manage')){
            return redirect('/dashboard')->withErrors(['If you see this error, report to Mihail with error code: 0x5545123']);
        }

        $e = Event::find($id);

        if(!$e){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        foreach($e->rosters as $roster){
            Booking::where('callsign', $roster->position->code)->where('event', 1)->where('time_end', '>', Carbon::parse($roster->from . 'z'))->where('time_start', '<', Carbon::parse($roster->to, 'z'))->delete();
        }

        $path = 'public/images/' . $e->cover_image;

        if(Storage::exists($path)){
            Storage::delete($path);
        }

        $e->delete();

        return redirect('/dashboard')->with('success', 'Event has been deleted sucessfully');
    }

    public function publish($id){
        if(!Auth::user()->hasPermission('events.manage')){
            return redirect('/dashborad')->withErrors(['Not authorized to publish events']);
        }

        $e = Event::find($id);

        if(!$e){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if($e->notification_sent){
            return redirect('/dashboard')->withErrors(['Event is already published']);
        }

        $this->sendNotificationForEvent($e);

        $e->notification_sent = true;
        $e->save();

        return redirect('/dashboard')->with('success', 'Event has been published sucessfully');
    }

    public function store(Request $request) {

        if(!Auth::user()->hasPermission('events.manage')){
            return redirect('/dashboard');
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'date' => 'required|date|after:today',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'description' => 'required|max:1000',
            'notes' => 'max:1000',
            'coverImage' => 'required|image|max:5120'
        ]);

        $event = new Event();
        $event->name=$request->name;
        $event->description=$request->description;

        $date = explode('/', $request->date);
        array_reverse($date);
        $date = implode('-', $date);

        $event->start=Carbon::parse($date . ' ' . $request->startTime . 'z'); //z to make Carbon understand it's UTC
        $event->end=Carbon::parse($date . ' ' . $request->endTime . 'z'); //z to make Carbon understand it's UTC

        if(!$event->end->isAfter($event->start)){
            return redirect()->back()->withErrors(['The event can\'t end before it started']);
        } else if($event->start->diffInHours($event->end) < 2){
            return redirect()->back()->withErrors(['The event end can\'t last less than 2 hours']);
        }

        $event->controller_message = $request->notes;

        $file = $request->file('coverImage');
        $extension = $file->getClientOriginalExtension();
        $filename = time().'.'.$extension;
        //$file->move(public_path('images'), $filename);
        $path = $file->storeAs('public/images', $filename);

        $event->cover_image = $filename;

        $event->save();

        if($request->has('sendNotification')){
            $this->sendNotificationForEvent($event);
            $event->notification_sent = true;
        } else {
            $event->notification_sent = false;
        }

        $event->save();


        return redirect('/dashboard')->with('success', 'Event created successfully');
    }

    protected function sendNotificationForEvent($event){
        if(!Auth::user()->hasPermission('events.manage')) return false;

        $webhook = config('vatadria.discord_webhooks.events');

        if(empty($webhook)) return false;

        Http::post($webhook, [
            'username'=>"ADRIA events",
            'content' => "<@&572743167439273985>",
             'embeds' => [
                 [
                    'title' => "Controllers, report availability for ".$event->name,
                    //"url" => "https://cc.vatadria.com/event/".$event->id."/availability/create/",
                    "url" => route('event.avl.create', $event->id),
                    'color' => '14783755',
                    "image" => [
                        'url' => 'https://cc.vatadria.com/storage/images/'.$event->cover_image
                    ],
                    "fields" => [
                        [
                        "name" => "When?",
                        "value" => Carbon::parse($event->start)->format("d.m.Y H:i")."-".Carbon::parse($event->end)->format("H:i"),
                        "inline" => true
                        ],

                        [
                        "name" => "What?",
                        "value" => str_replace('&nbsp;', '', strip_tags($event->description)),
                        "inline" => false
                        ]
                    ],
                ]
             ],
         ]);


    }

    public function showdata($id){

        $event = Event::find($id);

        if(!$event){ //Event doesn't exist
            return response()->json(['success'=>false,'reason'=>'The event doesn\'t exist']);
        }

        if(!$event->notification_sent && !Auth::user()->hasPermission('events.manage')){ //Event does exist but the notification hasn't been sent and the user is not events or above
            return response()->json(['success'=>false,'reason'=>'The event doesn\'t exist']);
        }

        $userData = $event->availabilities()->where('user_id', Auth::user()->id)->first(DB::raw('DATE_FORMAT(start, "%H:%i") as start, DATE_FORMAT(end, "%H:%i") as end, available'));

        if(!$event->roster_published){ //Event exists, the notification has been sent but the roster hasn't been published
            return response()->json(['success'=>true,'data'=>$event,'user_data'=>$userData,'user_roster'=>false]);
        }

        $event = Event::where('id', $id)->with([
            'rosters.mentors' => function($query){
                $query->select(DB::raw('*, IF(user_id='.(int)(Auth::user()->id).', 1, 0) as "is_current_user"'));
            },
            'rosters.mentors.user' => function($query){
                $query->select('id', 'first_name', 'last_name'); //Limit data that is loaded for the user so we don't load stuff like the access token and sent it in json
            },
            'rosters'=> function($query){
                $query->select(DB::raw('*, DATE_FORMAT(`from`, "%H:%i") as "from", DATE_FORMAT(`to`, "%H:%i") as "to", IF(user_id='. (int)(Auth::user()->id) .', 1, 0) as "is_current_user"'))->whereNotNull('user_id'); // cast to int to prevent SQL Injection just in case
            },
            'rosters.user'=> function($query){
                $query->select('id', 'first_name', 'last_name'); //Limit data that is loaded for the user so we don't load stuff like the access token and sent it in json
            },
            'rosters.position',
        ])->first(); //Load all the additional data

        /*$userRoster = $event->rosters()->where('user_id', Auth::user()->id)->with(['position','mentors.user'=>function($query){
            $query->select('id', 'first_name', 'last_name');
        }])->get(DB::raw('*, DATE_FORMAT(`from`, "%H:%i") as "from", DATE_FORMAT(`to`, "%H:%i") as "to"'));*/
        //^ this one is the initial one, it doesn't include the mentors search but i'll leave it here just in case

        //DB::connection()->enableQueryLog();
        $userRoster = $event->rosters()->with([
            'position',
            'mentors.user'=>function($query){
                $query->select('id', 'first_name', 'last_name'); //get only the id and the name for the mentor, so it doesn't incluse stuff like the access_toker and remember_token
            }
        ])
        ->where(function($q){
            $q->whereHas('mentors', function($query){
                $query->where('user_id', Auth::user()->id); // get items where the mentors include the user_id to be Auth::user()
            })
            ->orWhere('user_id', Auth::user()->id); // or where the user in the roster is Auth::user()
        })
        ->get(DB::raw('*, DATE_FORMAT(`from`, "%H:%i") as "from", DATE_FORMAT(`to`, "%H:%i") as "to"')); //reformat time so it doens't have the seconds and the milliseconds

        //dd(DB::getQueryLog());

        //Cleaning up names and sending only the public name of the users

        $elevated_access = auth()->user()->hasPermission('users.access.view');

        $event->rosters->each(function ($roster) use ($elevated_access) {

            $this->applyUserVisibility($roster->user, $elevated_access);

            $roster->mentors->each(function ($mentor) use ($elevated_access) {
                $this->applyUserVisibility($mentor->user, $elevated_access);
            });
        });

        $userRoster->each(function ($roster) use ($elevated_access) {

            $roster->mentors->each(function ($mentor) use ($elevated_access) {
                $this->applyUserVisibility($mentor->user, $elevated_access);
            });
        });

        return response()->json(['success'=>true, 'data'=>$event, 'user_data'=>$userData,'user_roster'=>$userRoster]);
    }

    private function applyUserVisibility(User $user, bool $elevated_access)
    {
        $user->setAttribute(
            'display_name',
            $elevated_access
                ? $user->first_name . ' ' . $user->last_name
                : $user->public_name
        );

        $user->makeHidden(['first_name', 'last_name']);
    }

    public function edit($id){

        if(!Auth::user()->hasPermission('events.manage')){
            return redirect()->route('dashboard')->withErrors(['You\'re not allowed to access this page']);
        }

        $event = Event::find($id);

        if(!$event){
            return redirect()->route('dashboard')->withErrors(['The event with that id doesn\'t exist']);
        }

        if($event->start <= Carbon::now()){
            return redirect()->route('dashboard')->withErrors(['The event has started']);
        }

        return view('event.edit', compact('event'));
    }

    public function patch(Request $request, $id){
        //TODO promeni svaki roster i mozda availability da bude u skladu sa novim vremenom
        if(!Auth::user()->hasPermission('events.manage')){
            return redirect()->route('dashboard')->withErrors(['You\'re not allowed to access this page']);
        }

        $event = Event::find($id);

        if(!$event){
            return redirect()->route('dashboard')->withErrors(['The event with that id doesn\'t exist']);
        }

        if($event->start <= Carbon::now()){
            return redirect()->route('dashboard')->withErrors(['The event has started']);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'date' => 'required|date|after_or_equal:today',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'description' => 'required|max:1000',
            'notes' => 'max:1000',
            'coverImage' => 'image|max:5120'
        ]);


        $event->name = $request->name;

        $date = explode('/', $request->date);
        array_reverse($date);
        $date = implode('-', $date);

        $event->start=Carbon::parse($date . ' ' . $request->startTime . 'z'); //z to make Carbon understand it's UTC
        $event->end=Carbon::parse($date . ' ' . $request->endTime . 'z'); //z to make Carbon understand it's UTC

        if(!$event->end->isAfter($event->start)){
            return redirect()->back()->withErrors(['The event can\'t end before it started']);
        } else if($event->start->diffInHours($event->end) < 2){
            return redirect()->back()->withErrors(['The event end can\'t last less than 2 hours']);
        }

        $event->description = $request->description;
        $event->controller_message = $request->notes;

        $file = $request->file('coverImage');

        if($file){
            $extension = $file->getClientOriginalExtension();
            $filename = time().'.'.$extension;
            //$file->move(public_path('images'), $filename);

            $file->storeAs('public/images', $filename);

            $oldfile = $event->cover_image;

            $event->cover_image = $filename;

            $path = 'public/images/' . $oldfile;

            if(Storage::exists($path)){
                Storage::delete($path);
            }
        }

        $event->save();

        return redirect()->route('dashboard')->with('success', 'Event updated successfully');
    }

}
