<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;

class BillingEntitlementService
{
    /**
     * Cek apakah user berhak menggunakan template tertentu (misal template premium).
     */
    public function canUseTemplate(User $user, Template $template): bool
    {
        if (!$template->is_premium) {
            return true;
        }

        return $user->hasActivePremium();
    }

    /**
     * Cek apakah user berhak mempublikasikan undangan dengan template premium.
     */
    public function canPublishInvitation(User $user, Invitation $invitation): bool
    {
        if ($invitation->template && $invitation->template->is_premium) {
            return $user->hasActivePremium();
        }

        return true;
    }

    /**
     * Cek batas maksimal tamu berdasarkan paket pengguna.
     */
    public function canAddGuest(User $user, Invitation $invitation): bool
    {
        if ($user->hasActivePremium()) {
            return true;
        }

        // Default paket gratis: 50 tamu
        $currentGuestsCount = $invitation->guests()->count();
        return $currentGuestsCount < 50;
    }
}
