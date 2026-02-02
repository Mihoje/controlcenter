<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventAvailability;
use App\Models\EventRoster;
use App\Models\EventPosition;
use App\Models\EventRosterMentor;
use App\Models\Position;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventRosterController extends Controller
{
    public function show($id){
        if(!Auth::user()->isEventOrAbove()){
            return redirect('/dashboard');
        }

        $event = Event::where('id', $id)->first(DB::raw('*, DATE_FORMAT(`start`, "%H:%i") as "start_time", DATE_FORMAT(`end`, "%H:%i") as "end_time"'));

        if(!$event){
            return redirect('/dashboard')->withErrors(['The event doesn\'t exist']);
        }

        if(Carbon::parse($event->end . 'z')->isBefore(Carbon::now())){
            return redirect('/dashboard')->withErrors(['The event already ended']);
        }

        if(!$event->notification_sent){ //idk if this needs to be here but it might make more complications in terms of publishing the roster
            return redirect('/dashboard')->withErrors(['The event is not published yet. Publish it first.']);
        }


        $avail = $event->availabilities()->with(['user'=>function($query){
            $query->select('id', 'first_name', 'last_name', 'rating_short');
        }])->get(DB::raw('*, DATE_FORMAT(`start`, "%H:%i") as "start", DATE_FORMAT(`end`, "%H:%i") as "end"'));

        $roster = $event->rosters()->with(['user','mentors.user' => function($query){
            $query->select('id', 'first_name', 'last_name');
        }, 'position'=>function($query){
            $query->orderBy('code', 'ASC');
        }])->whereNotNull('event_position_id')->get(DB::raw('*, DATE_FORMAT(`from`, "%H:%i") as "from", DATE_FORMAT(`to`, "%H:%i") as "to"'));

        $backupRoster = $event->rosters()->with(['user','mentors.user' => function($query){
            $query->select('id', 'first_name', 'last_name');
        }, 'position'=>function($query){
            $query->orderBy('code', 'ASC');
        }])->whereNull('event_position_id')->get(DB::raw('*, DATE_FORMAT(`from`, "%H:%i") as "from", DATE_FORMAT(`to`, "%H:%i") as "to"'));

        $rosterGroups = [];

        foreach ($roster as $r) {
            if(isset($rosterGroups[$r->position->id])){
                array_push($rosterGroups[$r->position->id], $r);
            } else {
                $rosterGroups[$r->position->id] = [$r];
            }
        }

        $pos = EventPosition::orderBy('code', 'ASC')->get();

        $positions = [];

        foreach ($pos as $position) {
            $prefix = explode('_', $position->code)[0];

            if(isset($positions[$prefix])){
                array_push($positions[$prefix], $position);
            } else {
                $positions[$prefix] = [$position];
            }
        }

        return view('event.roster.edit', compact('event', 'avail', 'roster', 'positions', 'rosterGroups', 'backupRoster'));
    }

    public function save(Request $request, $id){
        if(!Auth::user()->isEventOrAbove()){
            return redirect('/dashboard');
        }

        $event = Event::find($id);

        if(!$event){
            return redirect('/dashboard')->withErrors(['The event doesn\'t exist']);
        }

        if(Carbon::parse($event->end . 'z')->isBefore(Carbon::now())){
            return redirect('/dashboard')->withErrors(['The event already ended']);
        }

        if(!$event->notification_sent){ //idk if this needs to be here but it might make more complications in terms of publishing the roster
            return redirect('/dashboard')->withErrors(['The event is not published yet. Publish it first.']);
        }

        $groups = [];

        foreach ($request->all() as $key => $value) {
            if(preg_match('/\b(controller|posid|start|end|mentor)-[0-9]+\b/', $key)){
                $attr = explode('-', $key)[0];
                $id = explode('-', $key)[1];

                $groups[$id][$attr] = $value;
            }
        }

        if(count($groups) == 0){
            return redirect()->back()->withErrors(['You can\'t save an empty roster']);
        }

        ActivityLogController::info('OTHER', 'Saved roster for event ' . $event->id);

        $event->rosters()->delete();

        foreach ($groups as $key => $group) {

            $pos = null;

            if($group['posid'] != '/1'){
                $pos = EventPosition::find($group['posid']);

                if(!$pos){
                    return redirect()->back()->withErrors(['One of the positions is not valid']);
                }
            }

            $er = new EventRoster();
            $er->event()->associate($event);
            $er->position()->associate($pos);

            $from = Carbon::parse($event->isoDate . ' ' . $group['start']);
            $to = Carbon::parse($event->isoDate . ' ' . $group['end']);

            if($from->isBefore(Carbon::parse($event->start . 'z'))){
                return redirect()->back()->withErrors(['One of the roster start times is before the start of the event']);
            }

            if($to->isAfter(Carbon::parse($event->end . 'z'))){
                return redirect()->back()->withErrors(['One of the roster end times is after the end of the event']);
            }

            if($to->isBefore($from)){
                return redirect()->back()->withErrors(['One of the roster end times is before the start time']);
            }

            $er->from = $from;
            $er->to = $to;

            $controller = User::find($group['controller']);

            if($controller)
                $er->user()->associate($controller);

            $er->save();

            if($group['mentor']){
                $mentors = explode(',', $group['mentor']);


                foreach ($mentors as $mentor) {

                    $user = User::find($mentor);

                    if(!$user){
                        return redirect()->back()->withErrors(['Invalid user set as mentor']);
                    }

                    $erm = new EventRosterMentor();

                    $erm->user()->associate($user);
                    $erm->roster()->associate($er);
                    $erm->description = "Mentor";

                    $erm->save();

                }
            }
        }

        if(isset($_GET['publish'])){
            return redirect()->route('event.roster.publish', $event->id);
        }

        return redirect()->route('event.roster.create', $event->id)->with('success', 'Roster saved successfully');
    }

    public function bookPositions($id){
        if(!Auth::user()->isEventOrAbove()){
            return redirect('/dashboard');
        }

        $event = Event::find($id);

        if(!$event){
            return redirect('/dashboard')->withErrors(['The event doesn\'t exist']);
        }

        if(Carbon::parse($event->end . 'z')->isBefore(Carbon::now())){
            return redirect('/dashboard')->withErrors(['The event already ended']);
        }

        if(!$event->notification_sent){
            return redirect('/dashboard')->withErrors(['The event is not published yet. Publish it first.']);
        }

        $toSave = [];

        foreach ($event->rosters as $roster) {

            if(!$roster->position){
                continue;
            }

            Booking::where('callsign', $roster->position->code)->where('event', 1)->where('time_end', '>', Carbon::parse($roster->from . 'z'))->where('time_start', '<', Carbon::parse($roster->to, 'z'))->delete();
            Booking::where('callsign', $roster->position->code)->where('time_end', '>', Carbon::parse($roster->from . 'z'))->where('time_start', '<', Carbon::parse($roster->to, 'z'))->update(['deleted'=>1]);

            $booking = new Booking();

            $booking->source = "Event";
            $booking->callsign = $roster->position->code;
            $booking->position_id = Position::where('callsign', $roster->position->code)->first()->id; //pakao
            $booking->name = Auth::user()->name;
            $booking->time_start = Carbon::parse($roster->from . 'z');
            $booking->time_end = Carbon::parse($roster->to . 'z');
            $booking->user()->associate(Auth::user());
            $booking->event = true;


            //$booking->save(); //ovo se sklanja da ako neki debil u events dep stavi dva kretena u isto vreme da ne uzme samo zadnji booking
            array_push($toSave, $booking); //i ovo
        }

        foreach ($toSave as $b) { // i onda cuvamo ovde sve bookings-e
            $b->save();
        }

        ActivityLogController::info('OTHER', 'Booked positions for event ' . $event->id);

        return redirect()->back()->with('success', 'Positions successfully booked!');
    }

    public function publish(Request $request, $id){
        if(!Auth::user()->isEventOrAbove()){
            return redirect('/dashboard');
        }

        $event = Event::find($id);

        if(!$event){
            return redirect('/dashboard')->withErrors(['The event doesn\'t exist']);
        }

        if(Carbon::parse($event->end . 'z')->isBefore(Carbon::now())){
            return redirect('/dashboard')->withErrors(['The event already ended']);
        }

        if(!$event->notification_sent){ //idk if this needs to be here but it might make more complications in terms of publishing the roster
            return redirect('/dashboard')->withErrors(['The event is not published yet. Publish it first.']);
        }

        $this->sendNotificationForRoster($event);

        $event->roster_published = 1;
        $event->save();

        ActivityLogController::info('OTHER', 'Published roster for event ' . $event->id);

        return redirect('/dashboard')->with('success', 'Event sucessfully published');
    }

    protected function sendNotificationForRoster($event){
        $webhook = config('vatadria.discord_webhooks.events');

        if(empty($webhook)) return false;

        Http::post($webhook, [
            'username'=>"ADRIA events",
            'content' => "<@&572743167439273985>",
             'embeds' => [
                 [
                    'title' => sprintf("Roster has been published for %s", $event->name),
                    "url" => sprintf('%s?event=%d', route('dashboard'), $event->id),
                    'color' => '39423',
                    "image" => [
                        'url' => 'https://cc.vatadria.com/storage/images/'.$event->cover_image
                    ],
                ]
             ],
         ]);

    }
}
