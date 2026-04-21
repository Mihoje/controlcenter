<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Feedback extends Model
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    public function referenceUser()
    {
        return $this->belongsTo(User::class, 'reference_user_id');
    }

    public function referencePosition()
    {
        return $this->belongsTo(Position::class, 'reference_position_id');
    }

    public function getHeaderAttribute(){
        $controller = $this->referenceUser;
        $position = $this->referencePosition;
        $time = $this->time;

        $headingPart = "";

        if($controller){
            $headingPart = sprintf("%s (%s)", $controller->name, $controller->id);
        }

        if($position){
            $headingPart = sprintf("%s%s", strlen($headingPart) ? $headingPart . " - " : "", $position->callsign);
        }

        if($time) {
            $headingPart = sprintf("%s%s", strlen($headingPart) ? $headingPart . " - " : "", $time);
        }

        if(strlen($headingPart) == 0){
            $headingPart = "No controller data";
        }

        return $headingPart;
    }

    public function getFooterAttribute(){
        $submitter = $this->submitter;

        return sprintf("%s (%s)", $submitter->name, $submitter->id);
    }
}
