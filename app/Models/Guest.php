<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'name',
        'token',
        'phone_number',
        'max_attendees',
    ];

    protected static function booted()
    {
        static::creating(function ($guest) {
            if (empty($guest->token)) {
                $guest->token = Str::random(16);
            }
        });
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }

    public function getInvitationUrlAttribute(): string
    {
        return url('/i/' . $this->invitation->slug . '?to=' . urlencode($this->name) . '&t=' . $this->token);
    }
}
