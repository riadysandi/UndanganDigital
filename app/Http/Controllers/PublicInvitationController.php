<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicInvitationController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $invitation = Invitation::where('slug', $slug)->first();

        // 1. Cek keberadaan undangan
        if (!$invitation) {
            abort(404, 'Undangan tidak ditemukan.');
        }

        // 2. Cek status publikasi (Draft tidak boleh bocor ke publik sesuai PRD Bagian 8)
        if ($invitation->status !== 'published' || !$invitation->published_version_id) {
            return Inertia::render('Public/Unpublished', [
                'title' => $invitation->title,
                'status' => $invitation->status,
            ]);
        }

        // 3. Cek masa kedaluwarsa jika ada
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return Inertia::render('Public/Expired', [
                'title' => $invitation->title,
            ]);
        }

        // 4. Load published version snapshot yang immutable
        $publishedVersion = $invitation->publishedVersion;
        $events = $invitation->events;
        $guestbookEntries = $invitation->approvedGuestbookEntries;

        // 5. Cek parameter tamu personal (?to=Nama atau ?t=Token)
        $guestName = $request->query('to');
        $guestToken = $request->query('t');
        $guest = null;

        if ($guestToken) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('token', $guestToken)
                ->first();
            if ($guest) {
                $guestName = $guest->name;
            }
        }

        return Inertia::render('Public/InvitationView', [
            'invitation' => [
                'id' => $invitation->id,
                'title' => $invitation->title,
                'slug' => $invitation->slug,
                'published_at' => $invitation->published_at,
            ],
            'template' => $invitation->template,
            'design' => $publishedVersion ? $publishedVersion->design_json : null,
            'events' => $events,
            'guestbook' => $guestbookEntries,
            'guestName' => $guestName,
            'guestToken' => $guestToken,
            'guest' => $guest,
        ]);
    }

    public function submitRsvp(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'attendance_status' => ['required', 'in:attending,declined,tentative'],
            'attendee_count' => ['required', 'integer', 'min:1', 'max:10'],
            'message' => ['nullable', 'string', 'max:1000'],
            'guest_token' => ['nullable', 'string'],
        ]);

        $guest = null;
        if ($request->guest_token) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('token', $request->guest_token)
                ->first();
        }

        $rsvp = Rsvp::create([
            'invitation_id' => $invitation->id,
            'guest_id' => $guest ? $guest->id : null,
            'guest_name' => strip_tags($request->guest_name),
            'attendance_status' => $request->attendance_status,
            'attendee_count' => $request->attendee_count,
            'message' => $request->message ? strip_tags($request->message) : null,
            'submitted_at' => now(),
        ]);

        // Jika ada ucapan, otomatis tambahkan juga ke buku tamu
        if (!empty($request->message)) {
            GuestbookEntry::create([
                'invitation_id' => $invitation->id,
                'guest_name' => strip_tags($request->guest_name),
                'message' => strip_tags($request->message),
                'moderation_status' => 'approved',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas konfirmasi kehadiran Anda!',
            'rsvp' => $rsvp,
        ]);
    }

    public function submitGuestbook(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $entry = GuestbookEntry::create([
            'invitation_id' => $invitation->id,
            'guest_name' => strip_tags($request->guest_name),
            'message' => strip_tags($request->message),
            'moderation_status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doa dan ucapan Anda berhasil dikirimkan!',
            'entry' => $entry,
        ]);
    }
}
