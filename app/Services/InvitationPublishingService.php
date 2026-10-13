<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\InvitationVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class InvitationPublishingService
{
    public function __construct(
        protected BillingEntitlementService $entitlementService
    ) {}

    /**
     * Publikasikan undangan secara atomik: snapshot immutable versi baru.
     */
    public function publish(Invitation $invitation, User $user): InvitationVersion
    {
        // 1. Cek hak akses/entitlement
        if (!$this->entitlementService->canPublishInvitation($user, $invitation)) {
            throw new Exception("Template premium membutuhkan paket berbayar aktif untuk dipublikasikan.");
        }

        return DB::transaction(function () use ($invitation, $user) {
            $design = $invitation->design;
            if (!$design || empty($design->design_json)) {
                throw new Exception("Desain undangan belum dibuat atau masih kosong.");
            }

            // Hitung version number berikutnya
            $latestVersion = $invitation->versions()->max('version_number') ?? 0;
            $nextVersionNumber = $latestVersion + 1;

            // Buat snapshot immutable baru
            $version = InvitationVersion::create([
                'invitation_id' => $invitation->id,
                'version_number' => $nextVersionNumber,
                'design_json' => $design->design_json,
                'events_json' => $invitation->events()->get()->toArray(),
                'schema_version' => $design->schema_version ?? 1,
                'created_by' => $user->id,
                'published_at' => now(),
            ]);

            // Update pointer published_version_id secara atomik
            $invitation->update([
                'status' => 'published',
                'published_version_id' => $version->id,
                'published_at' => now(),
                'expires_at' => $invitation->expires_at ?? now()->addMonths(12),
            ]);

            // Audit log
            AuditLog::create([
                'actor_user_id' => $user->id,
                'action' => 'invitation.published',
                'subject_type' => Invitation::class,
                'subject_id' => $invitation->id,
                'metadata_json' => [
                    'version_number' => $nextVersionNumber,
                    'slug' => $invitation->slug,
                ],
            ]);

            return $version;
        });
    }

    /**
     * Unpublish undangan.
     */
    public function unpublish(Invitation $invitation, User $user): void
    {
        DB::transaction(function () use ($invitation, $user) {
            $invitation->update([
                'status' => 'draft',
            ]);

            AuditLog::create([
                'actor_user_id' => $user->id,
                'action' => 'invitation.unpublished',
                'subject_type' => Invitation::class,
                'subject_id' => $invitation->id,
                'metadata_json' => ['slug' => $invitation->slug],
            ]);
        });
    }
}
