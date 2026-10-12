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
                'total_checked_in' => $guests->whereNotNull('checked_in_at')->count(),
                'total_messages' => $guestbooks->count(),
            ],
            'canAddMore' => $this->entitlementService->canAddGuest($request->user(), $invitation),
        ]);
    }

    public function import(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $request->validate([
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
            'raw_text' => ['nullable', 'string', 'max:20000'],
        ]);

        $rows = [];

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle !== false) {
                $isFirst = true;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($isFirst) {
                        $isFirst = false;
                        // Skip header if matches "nama"
                        if (isset($data[0]) && stripos($data[0], 'nama') !== false) {
                            continue;
                        }
                    }
                    if (!empty($data[0])) {
                        $rows[] = [
                            'name' => trim($data[0]),
                            'phone' => isset($data[1]) ? trim($data[1]) : null,
                            'max' => isset($data[2]) ? (int)trim($data[2]) : 2,
                        ];
                    }
                }
                fclose($handle);
            }
        } elseif (!empty($request->raw_text)) {
            $lines = explode("\n", str_replace("\r", "", $request->raw_text));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $parts = array_map('trim', explode(',', $line));
                if (stripos($parts[0], 'nama') !== false) continue;
                $rows[] = [
                    'name' => $parts[0],
                    'phone' => $parts[1] ?? null,
                    'max' => isset($parts[2]) ? (int)$parts[2] : 2,
                ];
            }
        }

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'Tidak ada data tamu yang valid ditemukan.']);
        }

        $importedCount = 0;
        foreach ($rows as $row) {
            if (!$this->entitlementService->canAddGuest($request->user(), $invitation)) {
                return back()->with('warning', "Berhasil mengimpor {$importedCount} tamu. Kuota maksimal tercapai, silakan upgrade paket untuk menambahkan sisanya.");
            }

            Guest::create([
                'invitation_id' => $invitation->id,
                'name' => $row['name'],
                'phone_number' => $row['phone'] ?: null,
                'max_attendees' => $row['max'] > 0 ? $row['max'] : 2,
            ]);
            $importedCount++;
        }

        return back()->with('success', "Berhasil mengimpor {$importedCount} tamu sekaligus!");
    }

    public function export(Request $request, Invitation $invitation)
    {
        $this->authorize('view', $invitation);

        $guests = $invitation->guests()->with('rsvp')->latest()->get();
        $fileName = 'daftar-tamu-' . $invitation->slug . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($guests) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID Tamu',
                'Nama Tamu',
                'Nomor WhatsApp',
                'Kuota Hadir',
                'Status RSVP',
                'Jumlah Konfirmasi Hadir',
                'Ucapan / Pesan',
                'Status Check-in',
                'Waktu Check-in',
                'Tautan Undangan Khusus',
            ]);

            foreach ($guests as $guest) {
                $rsvp = $guest->rsvp;
                $rsvpStatus = match($rsvp?->attendance_status) {
                    'attending' => 'Hadir',
                    'declined' => 'Tidak Hadir',
                    'tentative' => 'Belum Pasti',
                    default => 'Belum Respon'
                };

                fputcsv($file, [
                    $guest->id,
                    $guest->name,
                    $guest->phone_number ?? '-',
                    $guest->max_attendees,
                    $rsvpStatus,
                    $rsvp ? $rsvp->attendee_count : 0,
                    $rsvp?->message ?? '-',
                    $guest->checked_in_at ? 'Sudah Hadir (Checked-in)' : 'Belum Hadir',
                    $guest->checked_in_at ? $guest->checked_in_at->format('d/m/Y H:i:s') : '-',
                    $guest->invitation_url,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function checkInPage(Request $request, Invitation $invitation): Response
    {
        $this->authorize('view', $invitation);

        $guests = $invitation->guests()->with('rsvp')->orderBy('name')->get();

        return Inertia::render('Guests/CheckIn', [
            'invitation' => $invitation,
            'guests' => $guests,
            'stats' => [
                'total_guests' => $guests->count(),
                'checked_in' => $guests->whereNotNull('checked_in_at')->count(),
                'remaining' => $guests->whereNull('checked_in_at')->count(),
            ],
        ]);
    }

    public function checkIn(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $request->validate([
            'query' => ['required', 'string'],
        ]);

        $searchTerm = trim($request->input('query'));

        // Search by token or exact guest id or case-insensitive name
        $guest = $invitation->guests()->with('rsvp')
            ->where(function ($q) use ($searchTerm) {
                $q->where('token', $searchTerm)
                  ->orWhere('id', is_numeric($searchTerm) ? (int)$searchTerm : 0)
                  ->orWhere('name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('phone_number', $searchTerm);
            })
            ->first();

        if (!$guest) {
            return response()->json([
                'success' => false,
                'message' => 'Tamu tidak ditemukan dalam daftar undangan.',
            ], 404);
        }

        $alreadyCheckedIn = !is_null($guest->checked_in_at);

        if (!$alreadyCheckedIn) {
            $guest->update(['checked_in_at' => now()]);
            $message = "Selamat Datang! Tamu \"{$guest->name}\" berhasil Check-in.";
        } else {
            $message = "Tamu \"{$guest->name}\" sudah check-in sebelumnya pada {$guest->checked_in_at->format('H:i:s d/m/Y')}.";
        }

        return response()->json([
            'success' => true,
            'already_checked_in' => $alreadyCheckedIn,
            'message' => $message,
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'token' => $guest->token,
                'phone_number' => $guest->phone_number,
                'max_attendees' => $guest->max_attendees,
                'checked_in_at' => $guest->checked_in_at?->format('H:i:s d/m/Y'),
                'rsvp' => $guest->rsvp ? [
                    'attendance_status' => $guest->rsvp->attendance_status,
                    'attendee_count' => $guest->rsvp->attendee_count,
                    'message' => $guest->rsvp->message,
                ] : null,
            ],
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
