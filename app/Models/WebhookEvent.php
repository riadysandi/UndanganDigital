<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_event_id',
        'event_type',
        'status',
        'sanitized_payload_json',
        'processed_at',
    ];

    protected $casts = [
        'sanitized_payload_json' => 'array',
        'processed_at' => 'datetime',
    ];
}
