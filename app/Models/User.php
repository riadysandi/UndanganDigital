<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActivePremium(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Cek subscription aktif
        $hasSub = $this->subscriptions()
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->exists();

        if ($hasSub) {
            return true;
        }

        // Cek order paket berbayar yang sukses (lifetime / 12 bulan)
        return $this->orders()
            ->where('status', 'paid')
            ->whereHas('plan', fn ($q) => $q->where('code', '!=', 'free'))
            ->exists();
    }
}
