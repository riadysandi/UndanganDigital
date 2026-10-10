<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invitation;
use App\Services\BillingEntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    public function __construct(
        protected BillingEntitlementService $entitlementService
    ) {}

    public function index(Request $request, Invitation $invitation): Response
    {
        $this->authorize('view', $invitation);

        $guests = $invitation->guests()->with('rsvp')->latest()->get();
        $rsvps = $invitation->rsvps()->latest()->get();
        $guestbooks = $invitation->guestbookEntries()->latest()->get();

        return Inertia::render('Guests/Index', [
            'invitation' => $invitation,
            'guests' => $guests,
            'rsvps' => $rsvps,
            'guestbooks' => $guestbooks,
            'stats' => [
                'total_guests' => $guests->count(),
                'total_attending' => $rsvps->where('attendance_status', 'attending')->sum('attendee_count'),
                'total_declined' => $rsvps->where('attendance_status', 'declined')->count(),
                'total_messages' => $guestbooks->count(),
            ],
            'canAddMore' => $this->entitlementService->canAddGuest($request->user(), $invitation),
        ]);
    }

    public function store(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        if (!$this->entitlementService->canAddGuest($request->user(), $invitation)) {
            return back()->withErrors([
                'name' => 'Batas maksimal kuota tamu untuk paket gratis (50 tamu) telah tercapai. Silakan upgrade ke paket Premium untuk tamu tanpa batas.',
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'max_attendees' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $guest = Guest::create([
            'invitation_id' => $invitation->id,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'max_attendees' => $request->max_attendees,
        ]);

        return back()->with('success', "Tamu \"{$guest->name}\" berhasil ditambahkan.");
    }

    public function destroy(Request $request, Invitation $invitation, Guest $guest)
    {
        $this->authorize('update', $invitation);

        if ($guest->invitation_id !== $invitation->id) {
            abort(403);
        }

        $guest->delete();

        return back()->with('success', 'Tamu berhasil dihapus.');
    }

    public function moderateGuestbook(Request $request, Invitation $invitation, int $entryId)
    {
        $this->authorize('update', $invitation);

        $entry = $invitation->guestbookEntries()->findOrFail($entryId);

        $request->validate([
            'status' => ['required', 'in:approved,pending,rejected'],
        ]);

        $entry->update([
            'moderation_status' => $request->status,
        ]);

        return back()->with('success', 'Status moderasi ucapan berhasil diperbarui.');
    }
}
