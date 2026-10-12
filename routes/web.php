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
    return Inertia::render('Welcome', [
        'templates' => Template::where('is_active', true)->take(4)->get(),
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
    Route::delete('/api/invitations/{invitation}/guests/{guest}', [GuestController::class, 'destroy'])->name('api.guests.destroy');
    Route::patch('/api/invitations/{invitation}/guestbook/{entryId}', [GuestController::class, 'moderateGuestbook'])->name('api.guestbook.moderate');

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
    Route::post('/templates/{template}/toggle', [AdminController::class, 'toggleTemplate'])->name('templates.toggle');
    Route::get('/invitations', [AdminController::class, 'invitations'])->name('invitations');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/service-requests', [\App\Http\Controllers\ServiceRequestController::class, 'adminIndex'])->name('service-requests');
    Route::patch('/service-requests/{serviceRequest}', [\App\Http\Controllers\ServiceRequestController::class, 'updateStatus'])->name('service-requests.update');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});
