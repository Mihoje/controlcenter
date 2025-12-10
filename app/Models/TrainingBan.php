<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

class TrainingBan extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuer(): BelongsTo{
        return $this->belongsTo(User::class, 'issued_by', 'id');
    }

    public function isActive(){
        return Carbon::parse($this->expires_on)->isAfter(Carbon::now());
    }

}