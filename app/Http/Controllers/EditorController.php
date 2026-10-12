<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditorController extends Controller
{
    public function show(Request $request, Invitation $invitation): Response
    {
        $this->authorize('update', $invitation);

        $invitation->load(['template', 'design', 'events', 'publishedVersion']);

        return Inertia::render('Editor/Index', [
            'invitation' => $invitation,
            'isPremium' => $request->user()->hasActivePremium(),
        ]);
    }

    public function getDesign(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        $design = $invitation->design;

        return response()->json([
            'success' => true,
            'design' => $design ? $design->design_json : null,
            'schema_version' => $design->schema_version ?? 1,
            'events' => $invitation->events,
        ]);
    }

    public function saveDesign(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $request->validate([
            'design' => ['required', 'array'],
            'design.schemaVersion' => ['required', 'integer'],
            'events' => ['nullable', 'array'],
        ]);

        $designData = $request->input('design');

        // Sanitasi dasar: pastikan teks dalam elemen di-escape dan tidak mengandung tag berbahaya
        if (isset($designData['sections']) && is_array($designData['sections'])) {
            foreach ($designData['sections'] as &$section) {
                if (isset($section['elements']) && is_array($section['elements'])) {
                    foreach ($section['elements'] as &$elem) {
                        if (isset($elem['text']) && is_string($elem['text'])) {
                            $elem['text'] = strip_tags($elem['text']);
                        }
                    }
                }
            }
        }

        // Simpan ke invitation_designs (draft)
        $invitation->design()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            [
                'design_json' => $designData,
                'schema_version' => $designData['schemaVersion'] ?? 1,
            ]
        );

        // Sync langsung ke versi published jika sudah dipublikasikan
        if ($invitation->published_version_id && $invitation->publishedVersion) {
            $invitation->publishedVersion->update([
                'design_json' => $designData,
            ]);
        }

        // Update events jika disertakan
        if ($request->has('events') && is_array($request->input('events'))) {
            foreach ($request->input('events') as $eventInput) {
                if (isset($eventInput['id'])) {
                    $ev = Event::where('invitation_id', $invitation->id)->find($eventInput['id']);
                    if ($ev) {
                        $ev->update([
                            'name' => $eventInput['name'] ?? $ev->name,
                            'starts_at' => $eventInput['starts_at'] ?? $ev->starts_at,
                            'ends_at' => $eventInput['ends_at'] ?? $ev->ends_at,
                            'venue_name' => $eventInput['venue_name'] ?? $ev->venue_name,
                            'address' => $eventInput['address'] ?? $ev->address,
                            'maps_url' => $eventInput['maps_url'] ?? $ev->maps_url,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Desain berhasil disimpan.',
            'saved_at' => now()->toIso8601String(),
        ]);
    }
}
