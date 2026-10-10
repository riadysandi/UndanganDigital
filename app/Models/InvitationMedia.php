<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'storage_key',
        'file_path',
        'media_type',
        'mime_type',
        'size_bytes',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'size_bytes' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
