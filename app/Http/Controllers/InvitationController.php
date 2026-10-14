<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\InvitationDesign;
use App\Models\Template;
use App\Services\BillingEntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function __construct(
        protected BillingEntitlementService $entitlementService
    ) {}

    public function create(Request $request): Response
    {
        $masterSlugs = [
            'ningrum-fairytale-3d',
            'celestia-starlight-3d',
            'victoria-luxury-3d',
            'alsa-royal-adat-3d',
            'bianca-botanical-3d',
            'eloise-romance-3d',
        ];
        $templates = Template::where('is_active', true)
            ->get()
            ->sortBy(function ($t) use ($masterSlugs) {
                $idx = array_search($t->slug, $masterSlugs);
                return $idx === false ? 999 : $idx;
            })
            ->values();

        return Inertia::render('Invitations/Create', [
            'templates' => $templates,
            'isPremium' => $request->user()->hasActivePremium(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'template_id' => ['required', 'exists:templates,id'],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:invitations,slug'],
        ]);

        $user = $request->user();
        $template = Template::findOrFail($request->template_id);

        // Validasi entitlement template premium
        if (!$this->entitlementService->canUseTemplate($user, $template)) {
            return back()->withErrors([
                'template_id' => 'Template ini bertanda Premium. Silakan upgrade ke paket Gold atau Platinum untuk menggunakannya.',
            ]);
        }

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title . '-' . Str::random(5));

        // Pastikan slug unik
        while (Invitation::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->title . '-' . Str::random(5));
        }

        $invitation = DB::transaction(function () use ($user, $template, $request, $slug) {
            $invitation = Invitation::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'title' => $request->title,
                'slug' => $slug,
                'status' => 'draft',
            ]);

            // Duplikasi konfigurasi default dari template ke draft user
            InvitationDesign::create([
                'invitation_id' => $invitation->id,
                'design_json' => $template->default_design_json,
                'schema_version' => $template->schema_version ?? 1,
            ]);

            // Buat default event
            Event::create([
                'invitation_id' => $invitation->id,
                'name' => 'Akad Nikah',
                'starts_at' => now()->addDays(30)->setTime(8, 0),
                'ends_at' => now()->addDays(30)->setTime(10, 0),
                'timezone' => 'WIB',
                'venue_name' => 'Lokasi Akad',
                'address' => 'Alamat lengkap gedung/tempat acara',
            ]);

            return $invitation;
        });

        return redirect(route('invitations.editor', $invitation->id))->with('success', 'Undangan berhasil dibuat! Mulai sesuaikan desain Anda.');
    }

    public function show(Request $request, Invitation $invitation): Response
    {
        $this->authorize('view', $invitation);

        $invitation->load(['template', 'events', 'design', 'publishedVersion'])
            ->loadCount(['guests', 'rsvps', 'guestbookEntries']);

        return Inertia::render('Invitations/Show', [
            'invitation' => $invitation,
        ]);
    }

    public function update(Request $request, Invitation $invitation)
    {
        $this->authorize('update', $invitation);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:invitations,slug,' . $invitation->id],
        ]);

        $invitation->update($validated);

        return back()->with('success', 'Informasi undangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Invitation $invitation)
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return redirect(route('dashboard'))->with('success', 'Undangan berhasil dihapus.');
    }
}
