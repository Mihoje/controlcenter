<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPosition extends Model
{
    public $timestamps = false;

    public $fillable = [
        'id', 'code', 'callsign', 'frequency',
    ];

    public function requiredRating(){
        return $this->belongsTo(Rating::class, 'required_rating');
    }

    public function rosters(){
        return $this->hasMany(EventRoster::class, 'event_position_id');
    }
}