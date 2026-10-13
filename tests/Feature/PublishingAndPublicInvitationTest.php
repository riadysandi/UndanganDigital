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

        $this->get('/i/dimas-anisa?preview=1')
            ->assertInertia(fn ($page) => $page->component('Public/Unpublished'));
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
        $this->assertNotEmpty($invitation->fresh()->publishedVersion->events_json);
    }

    public function test_editing_draft_does_not_change_the_published_design_or_events(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $invitation = Invitation::where('slug', 'dimas-anisa')->first();
        $this->actingAs($user)->postJson(route('api.publish', $invitation->id))->assertOk();
        $invitation->refresh()->load('publishedVersion');
        $publishedDesign = $invitation->publishedVersion->design_json;
        $publishedEvent = $invitation->events()->first();

        $this->actingAs($user)->putJson(route('api.design.save', $invitation->id), [
            'design' => [
                'schemaVersion' => 1,
                'sections' => [['id' => 'cover', 'elements' => [['text' => 'Draft only']]]],
            ],
            'events' => [[
                'id' => $publishedEvent->id,
                'name' => $publishedEvent->name,
                'venue_name' => 'Venue draft',
            ]],
        ])->assertOk();

        auth()->logout();
        $response = $this->get('/i/dimas-anisa');

        $response->assertInertia(fn ($page) => $page
            ->component('Public/InvitationView')
            ->where('design', $publishedDesign)
            ->where('events.0.venue_name', $publishedEvent->venue_name));

        $this->get('/i/dimas-anisa?preview=1')->assertInertia(fn ($page) => $page
            ->component('Public/InvitationView')
            ->where('design', $publishedDesign));

        $this->actingAs($user)->get('/i/dimas-anisa?preview=1')->assertInertia(fn ($page) => $page
            ->component('Public/InvitationView')
            ->where('design.sections.0.elements.0.text', 'Draft only'));
        $this->assertEquals('Venue draft', $publishedEvent->fresh()->venue_name);
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
            'moderation_status' => 'pending',
        ]);
    }

    public function test_draft_invitation_rejects_public_responses(): void
    {
        $invitation = Invitation::where('slug', 'dimas-anisa')->first();
        $invitation->update(['status' => 'draft']);

        $this->postJson('/api/public/dimas-anisa/rsvp', [
            'guest_name' => 'Fajar Pratama',
            'attendance_status' => 'attending',
            'attendee_count' => 1,
        ])->assertNotFound();

        $this->postJson('/api/public/dimas-anisa/guestbook', [
            'guest_name' => 'Fajar Pratama',
            'message' => 'Ucapan spam',
        ])->assertNotFound();

        $this->assertDatabaseMissing('rsvps', ['guest_name' => 'Fajar Pratama']);
        $this->assertDatabaseMissing('guestbook_entries', ['guest_name' => 'Fajar Pratama']);
    }

    public function test_public_guestbook_submissions_require_moderation(): void
    {
        $this->postJson('/api/public/dimas-anisa/guestbook', [
            'guest_name' => 'Nadia',
            'message' => 'Selamat menempuh hidup baru!',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('guestbook_entries', [
            'guest_name' => 'Nadia',
            'moderation_status' => 'pending',
        ]);
    }
}
