<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'version_number',
        'design_json',
        'events_json',
        'schema_version',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'design_json' => 'array',
        'events_json' => 'array',
        'schema_version' => 'integer',
        'published_at' => 'datetime',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
