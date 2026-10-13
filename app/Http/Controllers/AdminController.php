<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\InvitationDesign;
use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\ServiceRequest;
use App\Models\Template;
use App\Models\User;
use App\Services\InvitationPublishingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_invitations' => Invitation::count(),
            'published_invitations' => Invitation::where('status', 'published')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('amount'),
            'total_orders' => Order::count(),
            'pending_service_requests' => ServiceRequest::whereIn('status', ['pending', 'contacted'])->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        $recentOrders = Order::with(['user', 'plan'])->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentLogs = AuditLog::with('actor')->latest()->take(10)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers' => $recentUsers,
            'recentLogs' => $recentLogs,
        ]);
    }

    public function templates(Request $request): Response
    {
        $query = Template::withCount('invitations')->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('is_active', $request->input('status') === 'active');
        $templates = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Templates', [
            'templates' => $templates,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function toggleTemplate(Request $request, Template $template)
    {
        $field = $request->validate(['field' => ['required', 'in:is_premium,is_active']])['field'];
        $template->update([$field => !$template->{$field}]);
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'template.updated',
            'subject_type' => Template::class,
            'subject_id' => $template->id,
            'metadata_json' => [$field => $template->{$field}],
        ]);

        return back()->with('success', 'Status template berhasil diubah.');
    }

    public function storeTemplate(Request $request)
    {
        $data = $this->validateTemplate($request);
        $template = Template::create($data);
        $this->log($request, 'template.created', $template, ['slug' => $template->slug]);
        return back()->with('success', 'Template berhasil dibuat.');
    }

    public function updateTemplate(Request $request, Template $template)
    {
        $data = $this->validateTemplate($request, $template);
        $template->update($data);
        $this->log($request, 'template.updated', $template, ['slug' => $template->slug]);
        return back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroyTemplate(Request $request, Template $template)
    {
        if ($template->invitations()->exists()) return back()->with('error', 'Template yang sudah digunakan tidak dapat dihapus. Nonaktifkan template sebagai gantinya.');
        $this->log($request, 'template.deleted', $template, ['slug' => $template->slug]);
        $template->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }

    public function users(Request $request): Response
    {
        $query = User::withCount('invitations')->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('is_active', $request->input('status') === 'active');
        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function orders(Request $request): Response
    {
        $query = Order::with(['user', 'plan'])->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('order_number', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function invitations(Request $request): Response
    {
        $query = Invitation::with(['user', 'template', 'publishedVersion'])
            ->withCount(['guests', 'rsvps', 'guestbookEntries', 'media', 'versions', 'events'])
            ->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $invitations = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Invitations', [
            'invitations' => $invitations,
            'filters' => $request->only('q', 'status'),
            'owners' => User::where('role', 'customer')->orderBy('name')->get(['id', 'name', 'email']),
            'templates' => Template::orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }

    public function toggleUser(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        if ($user->isAdmin() && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) return back()->with('error', 'Superadmin aktif terakhir tidak dapat dinonaktifkan.');
        $user->update(['is_active' => !$user->is_active]);
        if (!$user->is_active) {
            \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $user->is_active ? 'user.activated' : 'user.suspended',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata_json' => ['email' => $user->email],
        ]);
        return back()->with('success', $user->is_active ? 'Akun pengguna diaktifkan.' : 'Akun pengguna dinonaktifkan.');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:12'],
            'role' => ['required', 'in:customer,admin'],
        ]);
        $user = User::create([...$data, 'is_active' => true, 'email_verified_at' => now()]);
        $this->log($request, 'user.created', $user, ['email' => $user->email, 'role' => $user->role]);
        return back()->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Akun yang sedang digunakan dikelola melalui menu profil.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:12'],
            'role' => ['required', 'in:customer,admin'],
        ]);
        if ($user->isAdmin() && $user->is_active && $data['role'] !== 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) return back()->with('error', 'Superadmin aktif terakhir tidak dapat diturunkan rolenya.');
        if (blank($data['password'] ?? null)) unset($data['password']);
        $user->update($data);
        $this->log($request, 'user.updated', $user, ['email' => $user->email, 'role' => $user->role]);
        return back()->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'Akun yang sedang digunakan tidak dapat dihapus.');
        if ($user->isAdmin() && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) return back()->with('error', 'Superadmin aktif terakhir tidak dapat dihapus.');
        if ($user->invitations()->exists() || $user->orders()->exists() || $user->subscriptions()->exists()) return back()->with('error', 'Akun memiliki data undangan atau transaksi. Nonaktifkan akun untuk mempertahankan riwayat.');
        $email = $user->email;
        $this->log($request, 'user.deleted', $user, ['email' => $email]);
        $user->delete();
        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function unpublishInvitation(Request $request, Invitation $invitation, InvitationPublishingService $publishingService)
    {
        $publishingService->unpublish($invitation, $request->user());
        return back()->with('success', 'Undangan berhasil diubah menjadi draft.');
    }

    public function storeInvitation(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:invitations,slug'],
        ]);
        $owner = User::findOrFail($data['user_id']);
        abort_if($owner->isAdmin(), 422, 'Undangan harus dimiliki akun customer.');
        $template = isset($data['template_id']) ? Template::findOrFail($data['template_id']) : null;

        $invitation = DB::transaction(function () use ($data, $template) {
            $invitation = Invitation::create([...$data, 'status' => 'draft']);
            InvitationDesign::create([
                'invitation_id' => $invitation->id,
                'design_json' => $template?->default_design_json ?? [
                    'schemaVersion' => 1,
                    'canvas' => ['width' => 1080, 'height' => 1920, 'background' => '#FDFBF7', 'primaryColor' => '#5D6B50', 'fontFamily' => 'Playfair Display'],
                    'theme' => 'modern',
                    'sections' => [],
                ],
                'schema_version' => $template?->schema_version ?? 1,
            ]);
            return $invitation;
        });
        $this->log($request, 'invitation.created', $invitation, ['slug' => $invitation->slug]);
        return back()->with('success', 'Undangan draft berhasil dibuat.');
    }

    public function updateInvitation(Request $request, Invitation $invitation)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('invitations', 'slug')->ignore($invitation->id)],
        ]);
        abort_if(User::findOrFail($data['user_id'])->isAdmin(), 422, 'Undangan harus dimiliki akun customer.');
        $invitation->update($data);
        $this->log($request, 'invitation.updated', $invitation, ['slug' => $invitation->slug]);
        return back()->with('success', 'Data undangan berhasil diperbarui.');
    }

    public function destroyInvitation(Request $request, Invitation $invitation)
    {
        if ($invitation->status === 'published') return back()->with('error', 'Unpublish undangan sebelum menghapusnya.');
        if ($invitation->versions()->exists() || $invitation->guests()->exists() || $invitation->rsvps()->exists() || $invitation->guestbookEntries()->exists() || $invitation->media()->exists() || $invitation->events()->exists() || Order::where('invitation_id', $invitation->id)->exists()) return back()->with('error', 'Undangan memiliki riwayat, order, acara, atau data tamu. Penghapusan ditolak untuk melindungi data tersebut.');
        $this->log($request, 'invitation.deleted', $invitation, ['slug' => $invitation->slug]);
        $invitation->delete();
        return back()->with('success', 'Undangan draft berhasil dihapus.');
    }

    public function auditLogs(Request $request): Response
    {
        $logs = AuditLog::with('actor')->latest()->paginate(25)->withQueryString();

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/Settings', [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'whatsapp_cs' => PlatformSetting::find('whatsapp_cs')?->value ?? config('services.whatsapp.number', '6281234567890'),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => now()->toDateTimeString(),
                'db_connection' => config('database.default'),
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_cs' => ['required', 'string', 'regex:/^[0-9]{8,20}$/'],
        ]);
        PlatformSetting::updateOrCreate(['key' => 'whatsapp_cs'], ['value' => $validated['whatsapp_cs']]);
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'platform.settings_updated',
            'subject_type' => PlatformSetting::class,
            'metadata_json' => ['keys' => ['whatsapp_cs']],
        ]);
        return back()->with('success', 'Nomor WhatsApp CS berhasil disimpan.');
    }

    private function validateTemplate(Request $request, ?Template $template = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('templates', 'slug')->ignore($template?->id)],
            'category' => ['required', 'string', 'max:80'],
            'thumbnail_path' => ['nullable', 'string', 'max:1000'],
            'default_design_json' => ['required', 'json'],
            'schema_version' => ['required', 'integer', 'min:1'],
            'is_premium' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
        $validated['default_design_json'] = json_decode($validated['default_design_json'], true, 512, JSON_THROW_ON_ERROR);
        return $validated;
    }

    private function log(Request $request, string $action, object $subject, array $metadata = []): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'metadata_json' => $metadata,
        ]);
    }
}
