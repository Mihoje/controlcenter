<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EventRosterMentor extends Model
{
    protected $fillable = ['user_id', 'event_roster_id', 'description'];

    public function roster(){
        return $this->belongsTo(EventRoster::class, 'event_roster_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
