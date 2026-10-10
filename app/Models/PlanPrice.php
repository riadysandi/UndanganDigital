<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'billing_interval',
        'amount',
        'currency',
        'provider_price_id',
        'active_from',
        'active_to',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active_from' => 'datetime',
        'active_to' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
