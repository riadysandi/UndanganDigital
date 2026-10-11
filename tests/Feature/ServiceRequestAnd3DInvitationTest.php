<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\ServiceRequest;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestAnd3DInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_service_request_page(): void
    {
        $response = $this->get('/bantu-buat');
        $response->assertStatus(200);
    }

    public function test_guest_can_submit_service_request(): void
    {
        $payload = [
            'customer_name' => 'Bambang Pamungkas',
            'whatsapp' => '081234567890',
            'email' => 'bambang@example.com',
            'package_selected' => 'complete',
            'template_preference' => 'royal-3d-envelope',
            'groom_name' => 'Bambang Pamungkas',
            'bride_name' => 'Triburna Dewi',
            'akad_date' => '2026-11-20',
            'notes' => 'Tolong buatkan versi 3D Wax Seal',
        ];

        $response = $this->post('/bantu-buat', $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('service_requests', [
            'customer_name' => 'Bambang Pamungkas',
            'whatsapp' => '081234567890',
            'package_selected' => 'complete',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_view_and_update_service_requests(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $request = ServiceRequest::create([
            'customer_name' => 'Dewi Sartika',
            'whatsapp' => '081987654321',
            'template_preference' => 'royal-3d-envelope',
            'groom_name' => 'Raden Mas',
            'bride_name' => 'Dewi Sartika',
            'package_selected' => 'express',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/service-requests');
        $response->assertStatus(200);

        $patchResponse = $this->actingAs($admin)->patch("/admin/service-requests/{$request->id}", [
            'status' => 'in_progress',
            'admin_notes' => 'Tim sedang memproses foto prewedding',
        ]);

        $patchResponse->assertSessionHas('success');
        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'status' => 'in_progress',
            'admin_notes' => 'Tim sedang memproses foto prewedding',
        ]);
    }

    public function test_3d_template_and_public_invitation_view(): void
    {
        $template = Template::create([
            'name' => 'Royal 3D Envelope & Wax Seal',
            'slug' => 'royal-3d-envelope',
            'category' => '3d',
            'thumbnail_path' => 'https://example.com/3d.jpg',
            'default_design_json' => ['is_3d' => true],
            'schema_version' => 1,
            'is_premium' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        $invitation = Invitation::create([
            'user_id' => $customer->id,
            'template_id' => $template->id,
            'title' => 'The Wedding of Dimas & Anisa',
            'slug' => 'dimas-anisa-3d',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $version = $invitation->versions()->create([
            'version_number' => 1,
            'design_json' => ['is_3d' => true, 'theme' => '3d-royal'],
            'schema_version' => 1,
            'created_by' => $customer->id,
            'published_at' => now(),
        ]);

        $invitation->update(['published_version_id' => $version->id]);

        $response = $this->get('/i/dimas-anisa-3d?to=Bpk.+Ridwan+Kamil');
        $response->assertStatus(200);
    }
}
