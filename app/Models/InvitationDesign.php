<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'design_json',
        'schema_version',
    ];

    protected $casts = [
        'design_json' => 'array',
        'schema_version' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
