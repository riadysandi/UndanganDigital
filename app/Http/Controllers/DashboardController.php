<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $invitations = $user->invitations()
            ->with(['template', 'publishedVersion'])
            ->withCount(['guests', 'rsvps'])
            ->latest()
            ->get();

        $templates = Template::where('is_active', true)->get();

        return Inertia::render('Dashboard/Index', [
            'invitations' => $invitations,
            'templates' => $templates,
            'isPremium' => $user->hasActivePremium(),
        ]);
    }
}
