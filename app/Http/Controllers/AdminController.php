<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\ServiceRequest;
use App\Models\Template;
use App\Models\User;
use App\Services\InvitationPublishingService;
use Illuminate\Http\Request;
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
            ->withCount(['guests', 'rsvps'])
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
        ]);
    }

    public function toggleUser(Request $request, User $user)
    {
        abort_if($user->isAdmin() || $user->is($request->user()), 403, 'Akun admin dan akun sendiri tidak dapat dinonaktifkan.');
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

    public function unpublishInvitation(Request $request, Invitation $invitation, InvitationPublishingService $publishingService)
    {
        $publishingService->unpublish($invitation, $request->user());
        return back()->with('success', 'Undangan berhasil diubah menjadi draft.');
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
}
