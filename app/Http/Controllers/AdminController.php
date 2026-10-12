<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Template;
use App\Models\User;
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

    public function templates(): Response
    {
        $templates = Template::withCount('invitations')->latest()->get();

        return Inertia::render('Admin/Templates', [
            'templates' => $templates,
        ]);
    }

    public function toggleTemplate(Request $request, Template $template)
    {
        $field = $request->input('field'); // 'is_premium' atau 'is_active'

        if (in_array($field, ['is_premium', 'is_active'])) {
            $template->update([
                $field => !$template->{$field},
            ]);
        }

        return back()->with('success', 'Status template berhasil diubah.');
    }

    public function users(): Response
    {
        $users = User::withCount('invitations')->latest()->paginate(20);

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::with(['user', 'plan', 'payments'])->latest()->paginate(20);

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
        ]);
    }

    public function invitations(): Response
    {
        $invitations = Invitation::with(['user', 'template', 'publishedVersion'])
            ->withCount(['guests', 'rsvps'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Invitations', [
            'invitations' => $invitations,
        ]);
    }

    public function auditLogs(): Response
    {
        $logs = AuditLog::with('actor')->latest()->paginate(25);

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/Settings', [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'whatsapp_cs' => '6281234567890',
            'system_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => now()->toDateTimeString(),
                'db_connection' => config('database.default'),
            ],
        ]);
    }
}
