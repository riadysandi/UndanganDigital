<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingAndPublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_invitation_viewable_without_login(): void
    {
        $response = $this->get('/i/dimas-anisa');

        $response->assertOk();
    }

    public function test_draft_invitation_cannot_be_viewed_by_public(): void
    {
        $invitation = Invitation::where('slug', 'dimas-anisa')->first();
        $invitation->update(['status' => 'draft']);

        $response = $this->get('/i/dimas-anisa');

        $response->assertOk();
        // Memastikan component Unpublished ditampilkan tanpa membocorkan konten
        $response->assertInertia(fn ($page) => $page->component('Public/Unpublished'));
    }

    public function test_publishing_creates_immutable_version_snapshot(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $invitation = Invitation::where('slug', 'dimas-anisa')->first();

        $initialVersionsCount = $invitation->versions()->count();

        $response = $this->actingAs($user)->postJson(route('api.publish', $invitation->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertEquals($initialVersionsCount + 1, $invitation->fresh()->versions()->count());
        $this->assertEquals('published', $invitation->fresh()->status);
    }

    public function test_guest_can_submit_rsvp_and_guestbook_message(): void
    {
        $response = $this->postJson('/api/public/dimas-anisa/rsvp', [
            'guest_name' => 'Fajar Pratama',
            'attendance_status' => 'attending',
            'attendee_count' => 2,
            'message' => 'Selamat menempuh hidup baru!',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('rsvps', [
            'guest_name' => 'Fajar Pratama',
            'attendance_status' => 'attending',
            'attendee_count' => 2,
        ]);

        $this->assertDatabaseHas('guestbook_entries', [
            'guest_name' => 'Fajar Pratama',
            'message' => 'Selamat menempuh hidup baru!',
            'moderation_status' => 'approved',
        ]);
    }
}
