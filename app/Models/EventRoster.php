<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRoster extends Model
{

    public function event(){
        return $this->belongsTo(Event::class);
    }

    public function position(){
        return $this->belongsTo(EventPosition::class, 'event_position_id');
    }

    public function mentors(){
        return $this->hasMany(EventRosterMentor::class, 'event_roster_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
