<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventAvailability;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EventAvailabilityController extends Controller
{

    public function create($eid){
        $event = Event::find($eid);

        if(!$event){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!$event->notification_sent && !Auth::user()->isEventOrAbove()){ //check if the event hasn't been posted and the user is not events or above
            return redirect('/dashboard')->withErrors(['Event dosen\'t exist']);
        }

        if(!Carbon::parse($event->start . 'z')->isAfter(Carbon::now())){ //if the event has already started
            return redirect('/dashboard')->withErrors(['You can\'t apply for an event that has already been started']);
        }

        return view('event.availability.create', compact('event'));
    }

    public function store(Request $request, $eid){
        $event = Event::find($eid);

        if(!$event){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!$event->notification_sent && !Auth::user()->isEventOrAbove()){ //check if the event hasn't been posted and the user is not events or above
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!Carbon::parse($event->start . 'z')->isAfter(Carbon::now())){ //if the event has already started
            return redirect('/dashboard')->withErrors(['You can\'t apply for an event that has already been started']);
        }

        if(Auth::user()->rating < 2){
            return redirect('/dashboard')->withErrors(['You need to have at least S1 controller rating to be able to report for events']);
        }

        $request->validate([
            'start_at' => 'required|date_format:H:i\z',
            'end_at' => 'required|date_format:H:i\z',
        ]);

        $availStart = Carbon::parse($event->isoDate . ' ' . $request->start_at);
        $availEnd = Carbon::parse($event->isoDate . ' ' . $request->end_at);

        if(Carbon::parse($event->start.'z')->isAfter($availStart)){
            return redirect()->back()->withErrors(['The start of your availability can\'t be before the start of the event']);
        }

        if(Carbon::parse($event->end.'z')->isBefore($availEnd)){
            return redirect()->back()->withErrors(['The end of your availability can\'t be after the end of the event']);
        }

        if($availEnd->isBefore($availStart)){
            return redirect()->back()->withErrors(['The end of your availability can\'t be before the start']);
        }

        if($event->lengthInHours < 3 && $availStart->diffInMinutes($availEnd) < 60){ //If event is less than 3h minimum avail time 60 minutes
            return redirect()->back()->withErrors(['Your availability can\'t be shorter than 60 minutes']);
        } else if($availStart->diffInMinutes($availEnd) < 90){ //Otherwise minimum avail time 90 minutes
            return redirect()->back()->withErrors(['Your availability can\'t be shorter than 90 minutes']);
        }

        if($event->availabilities()->where('user_id', Auth::user()->id)->first()){
            return redirect()->back()->withErrors(['You already applied for this event']);
        }

        $avail = new EventAvailability();
        $avail->user()->associate(Auth::user());
        $avail->event()->associate($event);
        $avail->start = $availStart;
        $avail->end = $availEnd;

        if($request->has('available')){
            $avail->available = true;
        } else {
            $avail->available = false;
        }

        $avail->save();

        return redirect('/dashboard')->with('success', 'Event availability registered');
    }

    public function edit(Request $request, $eid){
        $event = Event::find($eid);

        if(!$event){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!$event->notification_sent && !Auth::user()->isEventOrAbove()){ //check if the event hasn't been posted and the user is not events or above
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!Carbon::parse($event->start . 'z')->isAfter(Carbon::now())){ //if the event has already started
            return redirect('/dashboard')->withErrors(['You can\'t apply for an event that has already been started']);
        }

        $avail = $event->availabilities()->where('user_id', Auth::user()->id)->first();

        if(!$avail){
            return redirect('/dashboard')->withErrors(['You didn\'t apply for this event']);
        }

        return view('event.availability.edit', compact('event', 'avail'));
    }

    public function update(Request $request, $eid){
        $event = Event::find($eid);

        if(!$event){
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!$event->notification_sent && !Auth::user()->isEventOrAbove()){ //check if the event hasn't been posted and the user is not events or above
            return redirect('/dashboard')->withErrors(['Event doens\'t exist']);
        }

        if(!Carbon::parse($event->start . 'z')->isAfter(Carbon::now())){ //if the event has already started
            return redirect('/dashboard')->withErrors(['You can\'t apply for an event that has already been started']);
        }

        $request->validate([
            'start_at' => 'required|date_format:H:i\z',
            'end_at' => 'required|date_format:H:i\z',
        ]);

        $availStart = Carbon::parse($event->isoDate . ' ' . $request->start_at);
        $availEnd = Carbon::parse($event->isoDate . ' ' . $request->end_at);

        if(Carbon::parse($event->start.'z')->isAfter($availStart)){
            return redirect()->back()->withErrors(['The start of your availability can\'t be before the start of the event']);
        }

        if(Carbon::parse($event->end.'z')->isBefore($availEnd)){
            return redirect()->back()->withErrors(['The end of your availability can\'t be after the end of the event']);
        }

        if($availEnd->isBefore($availStart)){
            return redirect()->back()->withErrors(['The end of your availability can\'t be before the start']);
        }

        if($event->lengthInHours < 3 && $availStart->diffInMinutes($availEnd) < 60){ //If event is less than 3h minimum avail time 60 minutes
            return redirect()->back()->withErrors(['Your availability can\'t be shorter than 60 minutes']);
        } else if($availStart->diffInMinutes($availEnd) < 90){ //Otherwise minimum avail time 90 minutes
            return redirect()->back()->withErrors(['Your availability can\'t be shorter than 90 minutes']);
        }

        $avail = $event->availabilities()->where('user_id', Auth::user()->id)->first();

        if(!$avail){
            return redirect()->back()->withErrors(['You didn\'t apply for this event']);
        }

        $avail->user()->associate(Auth::user());
        $avail->event()->associate($event);
        $avail->start = $availStart;
        $avail->end = $availEnd;

        if($request->has('available')){
            $avail->available = true;
        } else {
            $avail->available = false;
        }

        $avail->save();

        return redirect('/dashboard')->with('success', 'Event availability updated');
    }

}