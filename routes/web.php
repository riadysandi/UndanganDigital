<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PublicInvitationController;
use App\Models\Template;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
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

    return Inertia::render('Welcome', [
        'templates' => $templates,
    ]);
})->name('home');

// Pricing Page
Route::get('/pricing', [BillingController::class, 'index'])->name('pricing');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::redirect('/admin/login', '/login')->name('admin.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public Invitation Views & Interactivity (No Auth Required)
Route::get('/demo/{template:slug}', [PublicInvitationController::class, 'demo'])->name('templates.demo');

Route::prefix('i')->group(function () {
    Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('public.invitation');
});

Route::prefix('api/public')->group(function () {
    Route::post('/{slug}/rsvp', [PublicInvitationController::class, 'submitRsvp'])->middleware('throttle:30,1');
    Route::post('/{slug}/guestbook', [PublicInvitationController::class, 'submitGuestbook'])->middleware('throttle:30,1');
});

// Webhooks
Route::post('/api/webhooks/{provider}', [BillingController::class, 'webhook'])->name('webhook');

// Authenticated Customer Area
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invitations
    Route::get('/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::get('/invitations/{invitation}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::patch('/invitations/{invitation}', [InvitationController::class, 'update'])->name('invitations.update');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    // Visual Canvas Editor
    Route::get('/invitations/{invitation}/editor', [EditorController::class, 'show'])->name('invitations.editor');
    Route::get('/api/invitations/{invitation}/design', [EditorController::class, 'getDesign'])->name('api.design.get');
    Route::put('/api/invitations/{invitation}/design', [EditorController::class, 'saveDesign'])->name('api.design.save');
    Route::post('/api/invitations/{invitation}/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('api.invitations.upload');

    // Publish & Unpublish
    Route::post('/api/invitations/{invitation}/publish', [\App\Http\Controllers\PublishController::class, 'publish'])->name('api.publish');
    Route::post('/api/invitations/{invitation}/unpublish', [\App\Http\Controllers\PublishController::class, 'unpublish'])->name('api.unpublish');

    // Guests & RSVP Management
    Route::get('/invitations/{invitation}/guests', [GuestController::class, 'index'])->name('invitations.guests');
    Route::post('/api/invitations/{invitation}/guests', [GuestController::class, 'store'])->name('api.guests.store');
    Route::post('/api/invitations/{invitation}/guests/import', [GuestController::class, 'import'])->name('api.guests.import');
    Route::get('/invitations/{invitation}/guests/export', [GuestController::class, 'export'])->name('invitations.guests.export');
    Route::delete('/api/invitations/{invitation}/guests/{guest}', [GuestController::class, 'destroy'])->name('api.guests.destroy');
    Route::patch('/api/invitations/{invitation}/guestbook/{entryId}', [GuestController::class, 'moderateGuestbook'])->name('api.guestbook.moderate');
    Route::get('/invitations/{invitation}/checkin', [GuestController::class, 'checkInPage'])->name('invitations.checkin');
    Route::post('/api/invitations/{invitation}/guests/check-in', [GuestController::class, 'checkIn'])->name('api.guests.checkin');

    // Billing & Orders
    Route::post('/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/orders/{order}/pay', [BillingController::class, 'pay'])->name('billing.pay');
    Route::post('/api/sandbox/simulate-payment/{order}', [BillingController::class, 'simulatePayment'])->name('billing.simulate');
});

// Layanan Jasa Pembuatan "Dibantu Tim Kami" (Terima Beres)
Route::get('/bantu-buat', [\App\Http\Controllers\ServiceRequestController::class, 'create'])->name('services.request');
Route::post('/bantu-buat', [\App\Http\Controllers\ServiceRequestController::class, 'store'])->name('services.store');

// Admin Panel Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
    Route::post('/templates', [AdminController::class, 'storeTemplate'])->name('templates.store');
    Route::put('/templates/{template}', [AdminController::class, 'updateTemplate'])->name('templates.update');
    Route::delete('/templates/{template}', [AdminController::class, 'destroyTemplate'])->name('templates.destroy');
    Route::post('/templates/{template}/toggle', [AdminController::class, 'toggleTemplate'])->name('templates.toggle');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::post('/invitations', [AdminController::class, 'storeInvitation'])->name('invitations.store');
    Route::put('/invitations/{invitation}', [AdminController::class, 'updateInvitation'])->name('invitations.update');
    Route::delete('/invitations/{invitation}', [AdminController::class, 'destroyInvitation'])->name('invitations.destroy');
    Route::post('/invitations/{invitation}/unpublish', [AdminController::class, 'unpublishInvitation'])->name('invitations.unpublish');
    Route::get('/invitations', [AdminController::class, 'invitations'])->name('invitations');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/service-requests', [\App\Http\Controllers\ServiceRequestController::class, 'adminIndex'])->name('service-requests');
    Route::post('/service-requests', [\App\Http\Controllers\ServiceRequestController::class, 'storeAdmin'])->name('service-requests.store');
    Route::patch('/service-requests/{serviceRequest}', [\App\Http\Controllers\ServiceRequestController::class, 'updateStatus'])->name('service-requests.update');
    Route::delete('/service-requests/{serviceRequest}', [\App\Http\Controllers\ServiceRequestController::class, 'destroyAdmin'])->name('service-requests.destroy');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
