<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    public function create(Request $request): Response
    {
        $templates = Template::where('is_active', true)->get();

        return Inertia::render('Services/RequestAssistance', [
            'templates' => $templates,
            'defaultName' => $request->user()?->name ?? '',
            'defaultPhone' => $request->user()?->phone_number ?? '',
            'defaultEmail' => $request->user()?->email ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'template_preference' => ['required', 'string'],
            'groom_name' => ['required', 'string', 'max:255'],
            'groom_parents' => ['nullable', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'bride_parents' => ['nullable', 'string', 'max:255'],
            'akad_date' => ['nullable', 'string', 'max:255'],
            'akad_venue' => ['nullable', 'string', 'max:255'],
            'resepsi_date' => ['nullable', 'string', 'max:255'],
            'resepsi_venue' => ['nullable', 'string', 'max:255'],
            'photo_drive_link' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'package_selected' => ['required', 'string'],
        ]);

        $serviceRequest = ServiceRequest::create([
            ...$validated,
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        if ($request->user()) {
            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'service_request.created',
                'subject_type' => ServiceRequest::class,
                'subject_id' => $serviceRequest->id,
                'metadata_json' => [
                    'customer_name' => $serviceRequest->customer_name,
                    'package' => $serviceRequest->package_selected,
                ],
            ]);
        }

        // WhatsApp direct formatted message to our team CS/Designer
        $waMessage = "Halo Tim Undangan.id, saya ingin dibantu buatkan undangan digital (Terima Beres):\n\n"
            . "• Nama Pemesan: {$serviceRequest->customer_name}\n"
            . "• Paket: {$serviceRequest->package_selected}\n"
            . "• Pilihan Tema: {$serviceRequest->template_preference}\n"
            . "• Mempelai: {$serviceRequest->groom_name} & {$serviceRequest->bride_name}\n"
            . "• Akad: {$serviceRequest->akad_date} ({$serviceRequest->akad_venue})\n"
            . "• Resepsi: {$serviceRequest->resepsi_date} ({$serviceRequest->resepsi_venue})\n"
            . ($serviceRequest->photo_drive_link ? "• Link Foto: {$serviceRequest->photo_drive_link}\n" : "")
            . ($serviceRequest->notes ? "• Catatan Khusus: {$serviceRequest->notes}\n" : "");

        $waUrl = "https://api.whatsapp.com/send?phone=6281234567890&text=" . urlencode($waMessage);

        return back()->with([
            'success' => 'Permintaan bantuan Anda berhasil dikirim! Tim kami akan segera memprosesnya.',
            'whatsapp_url' => $waUrl,
        ]);
    }

    public function adminIndex(Request $request): Response
    {
        $requests = ServiceRequest::with('user')->latest()->paginate(20);

        return Inertia::render('Admin/ServiceRequests', [
            'requests' => $requests,
        ]);
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,contacted,in_progress,completed,cancelled'],
            'created_invitation_url' => ['nullable', 'string', 'max:500'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $serviceRequest->update($validated);

        return back()->with('success', 'Status pengerjaan berhasil diperbarui.');
    }
}
