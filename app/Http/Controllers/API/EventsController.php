<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Event;

class EventsController extends Controller
{
    
    public function upcomingEvents(){
        //$events = DB::table('events')->select('*', DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"), DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'"))->where( 'end', '>=', Carbon::now() )->where('is_roster_published', 1)->orderBy('start', 'ASC')->get();
        $events = Event::where('end', '>', Carbon::now())->where('notification_sent', 1)->orderBy('start', 'ASC')->get(['id', 'name', 'description', 'cover_image', 'start', 'end', DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"),  DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'")]);
        return response()->json($events);
    }

    public function soonEvent(Request $request, int $hours){

        //$events = DB::table('events')->select('*', DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"), DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'"))->where( 'start', '>=', Carbon::now() )->where('start', '<=', Carbon::now()->add($hours, 'hours'))->where('is_roster_published', 1)->orderBy('start', 'ASC')->limit(1)->get();
        $event = Event::where('start', '>=', Carbon::now())->where('start', '<=', Carbon::now()->addHours($hours))->where('notification_sent', 1)->orderBy('start', 'ASC')->first(['id', 'name', 'description', 'cover_image', 'start', 'end', DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"),  DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'")]);

        return response()->json($event);
    }

    public function futureEvents(){
        $events = Event::where('start', '>', Carbon::now())->orderBy('start', 'ASC')->get(['id', 'name', 'description', 'cover_image', 'start', 'end', 'roster_published', DB::raw('notification_sent as published'), DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"),  DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'")]);

        return response()->json($events);
    }

    public function getEventDetails($id){
        $event = Event::where('id', $id)->where('start', '>', Carbon::now())->orderBy('start', 'ASC')->first(['id', 'name', 'description', 'cover_image', 'start', 'end', 'roster_published', DB::raw('notification_sent as published'), DB::raw("DATE_FORMAT(start, '%d.%m.%Y') as 'date'"), DB::raw("DATE_FORMAT(start, '%H:%i') as 'start_time'"),  DB::raw("DATE_FORMAT(end, '%H:%i') as 'end_time'")]);

        if(!$event){
            return response()->json(['success'=>false,'message'=>'Event not found']);
        }

        return response()->json(['success'=>true,'event'=>$event]);
    }

}