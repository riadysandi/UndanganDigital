<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\PlatformSetting;
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

        $csNumber = PlatformSetting::find('whatsapp_cs')?->value ?? config('services.whatsapp.number', '6281234567890');
        $waUrl = "https://api.whatsapp.com/send?phone={$csNumber}&text=" . urlencode($waMessage);

        return back()->with([
            'success' => 'Permintaan bantuan Anda berhasil dikirim! Tim kami akan segera memprosesnya.',
            'whatsapp_url' => $waUrl,
        ]);
    }

    public function adminIndex(Request $request): Response
    {
        $query = ServiceRequest::with('user')->latest();
        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('customer_name', 'like', "%{$term}%")
                ->orWhere('whatsapp', 'like', "%{$term}%")
                ->orWhere('groom_name', 'like', "%{$term}%")
                ->orWhere('bride_name', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $requests = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/ServiceRequests', [
            'requests' => $requests,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $data = $this->validateAdminRequest($request);
        $serviceRequest = ServiceRequest::create($data);
        $this->logAdminRequest($request, 'service_request.created', $serviceRequest);
        return back()->with('success', 'Permintaan jasa berhasil dibuat.');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $serviceRequest->update($this->validateAdminRequest($request));
        $this->logAdminRequest($request, 'service_request.updated', $serviceRequest);

        return back()->with('success', 'Status pengerjaan berhasil diperbarui.');
    }

    public function destroyAdmin(Request $request, ServiceRequest $serviceRequest)
    {
        $this->logAdminRequest($request, 'service_request.deleted', $serviceRequest);
        $serviceRequest->delete();
        return back()->with('success', 'Permintaan jasa berhasil dihapus.');
    }

    private function validateAdminRequest(Request $request): array
    {
        return $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'template_preference' => ['required', 'string', 'max:255'],
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
            'package_selected' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:pending,contacted,in_progress,completed,cancelled'],
            'created_invitation_url' => ['nullable', 'string', 'max:500'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function logAdminRequest(Request $request, string $action, ServiceRequest $serviceRequest): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => ServiceRequest::class,
            'subject_id' => $serviceRequest->id,
            'metadata_json' => ['status' => $serviceRequest->status],
        ]);
    }
}
