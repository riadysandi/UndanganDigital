<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_customer_can_create_invitation_from_free_template(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $freeTemplate = Template::where('is_premium', false)->first();

        $response = $this->actingAs($user)->post('/invitations', [
            'title' => 'Pernikahan Budi & Dewi',
            'template_id' => $freeTemplate->id,
            'slug' => 'budi-dewi',
        ]);

        $invitation = Invitation::where('slug', 'budi-dewi')->first();
        $this->assertNotNull($invitation);
        $this->assertEquals($user->id, $invitation->user_id);
        $response->assertRedirect(route('invitations.editor', $invitation->id));
    }

    public function test_free_customer_cannot_use_premium_template_without_active_subscription(): void
    {
        $freeUser = User::factory()->create(['role' => 'customer']);
        $premiumTemplate = Template::where('is_premium', true)->first();

        $response = $this->actingAs($freeUser)->post('/invitations', [
            'title' => 'Pernikahan Mewah',
            'template_id' => $premiumTemplate->id,
            'slug' => 'pernikahan-mewah',
        ]);

        $response->assertSessionHasErrors('template_id');
        $this->assertDatabaseMissing('invitations', ['slug' => 'pernikahan-mewah']);
    }

    public function test_tenant_isolation_user_cannot_access_or_edit_another_users_invitation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $invitationA = Invitation::create([
            'user_id' => $userA->id,
            'title' => 'Undangan Milik User A',
            'slug' => 'undangan-a',
            'status' => 'draft',
        ]);

        // User B mencoba membuka editor User A -> 403 Forbidden
        $response = $this->actingAs($userB)->get(route('invitations.editor', $invitationA->id));
        $response->assertStatus(403);

        // User B mencoba menghapus undangan User A -> 403 Forbidden
        $deleteResponse = $this->actingAs($userB)->delete(route('invitations.destroy', $invitationA->id));
        $deleteResponse->assertStatus(403);
    }

    public function test_autosave_design_json_schema_v1(): void
    {
        $user = User::where('email', 'pengantin@undangan.id')->first();
        $invitation = Invitation::where('slug', 'dimas-anisa')->first();

        $newDesign = [
            'schemaVersion' => 1,
            'canvas' => [
                'width' => 1080,
                'height' => 1920,
                'background' => '#FFF5F5',
            ],
            'sections' => [
                [
                    'id' => 'cover',
                    'type' => 'cover',
                    'elements' => [
                        ['id' => 'title', 'type' => 'text', 'text' => 'Updated Wedding Title'],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->putJson(route('api.design.save', $invitation->id), [
            'design' => $newDesign,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertEquals('Updated Wedding Title', $invitation->fresh()->design->design_json['sections'][0]['elements'][0]['text']);
    }
}
