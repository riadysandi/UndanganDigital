<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'title',
        'slug',
        'status',
        'published_version_id',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function design(): HasOne
    {
        return $this->hasOne(InvitationDesign::class);
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(InvitationVersion::class, 'published_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InvitationVersion::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(InvitationMedia::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class)->orderBy('starts_at', 'asc');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class)->orderBy('name', 'asc');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class)->orderBy('submitted_at', 'desc');
    }

    public function guestbookEntries(): HasMany
    {
        return $this->hasMany(GuestbookEntry::class)->orderBy('created_at', 'desc');
    }

    public function approvedGuestbookEntries(): HasMany
    {
        return $this->hasMany(GuestbookEntry::class)->where('moderation_status', 'approved')->orderBy('created_at', 'desc');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_version_id !== null;
    }
}
