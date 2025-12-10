<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedUser extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id', 'id');
    }

    public function issuer(): BelongsTo{
        return $this->belongsTo(User::class, 'issued_by', 'id');
    }

}