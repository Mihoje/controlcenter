<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    public $fillable = [
        'id', 'name', 'description', 'start', 'end', 'controller_message', 'cover_image', 'roster_published', 'roster_notes',
    ];

    public function availabilities(){
        return $this->hasMany(EventAvailability::class);
    }

    public function rosters(){
        return $this->hasMany(EventRoster::class);
    }

    public function hasUserReported(User $user){
        return count($this->availabilities()->where('user_id', $user->id)->get()) ? true : false;
    }

    public function isUserAvailable(User $user){
        if(!$this->hasUserReported($user)) return false;

        return $this->availabilities()->where('user_id', $user->id)->where('available', 1)->count() ? true : false;
    }

    public function getDateAttribute(){
        return Carbon::parse($this->start.'z')->format('l, d/m/Y');
    }

    public function getIsoDateAttribute(){
        return Carbon::parse($this->start.'z')->format('Y-m-d');
    }

    public function getStartTimeAttribute(){
        return Carbon::parse($this->start.'z')->format('H:i');
    }

    public function getEndTimeAttribute(){
        return Carbon::parse($this->end.'z')->format('H:i');
    }

    public function getTimeLengthAttribute(){
        return $this->date . ' ' . $this->startTime . '-' . $this->endTime;
    }

    public function getLengthInMinutesAttribute(){
        return Carbon::parse($this->start.'z')->diffInMinutes(Carbon::parse($this->end.'z'));
    }

    public function getLengthInHoursAttribute(){
        return Carbon::parse($this->start.'z')->diffInHours(Carbon::parse($this->end.'z'));
    }

    
}
