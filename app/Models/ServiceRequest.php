<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'whatsapp',
        'email',
        'template_preference',
        'groom_name',
        'groom_parents',
        'bride_name',
        'bride_parents',
        'akad_date',
        'akad_venue',
        'resepsi_date',
        'resepsi_venue',
        'photo_drive_link',
        'notes',
        'package_selected',
        'status',
        'created_invitation_url',
        'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
