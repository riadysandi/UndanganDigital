<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Plans (Paket Langganan & Fitur)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // 'free', 'gold', 'platinum'
            $table->text('description')->nullable();
            $table->json('features_json')->nullable(); // max_guests, custom_domain, remove_watermark, etc.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Plan Prices
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('billing_interval')->default('once'); // 'once', 'monthly', 'yearly'
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->string('provider_price_id')->nullable();
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_to')->nullable();
            $table->timestamps();
        });

        // 3. Templates
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('modern'); // 'modern', 'rustic', 'floral', 'traditional'
            $table->string('thumbnail_path')->nullable();
            $table->json('default_design_json');
            $table->integer('schema_version')->default(1);
            $table->boolean('is_premium')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 4. Invitations
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index(); // 'draft', 'published', 'archived'
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 5. Invitation Designs (Draft yang sedang diedit)
        Schema::create('invitation_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->json('design_json');
            $table->integer('schema_version')->default(1);
            $table->timestamps();
        });

        // 6. Invitation Versions (Immutable published snapshots)
        Schema::create('invitation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('design_json');
            $table->integer('schema_version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['invitation_id', 'version_number']);
        });

        // Foreign key for invitations.published_version_id
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreign('published_version_id')->references('id')->on('invitation_versions')->nullOnDelete();
        });

        // 7. Invitation Media
        Schema::create('invitation_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('storage_key');
            $table->string('file_path');
            $table->string('media_type')->default('image'); // 'image', 'audio', 'video'
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        // 8. Events (Akad Nikah, Resepsi, etc.)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('name'); // contoh: 'Akad Nikah', 'Resepsi Pernikahan'
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('timezone', 10)->default('WIB');
            $table->string('venue_name');
            $table->text('address')->nullable();
            $table->string('maps_url', 1000)->nullable();
            $table->timestamps();
        });

        // 9. Guests
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('name');
            $table->string('token')->index();
            $table->string('phone_number')->nullable();
            $table->integer('max_attendees')->default(2);
            $table->timestamps();

            $table->unique(['invitation_id', 'token']);
        });

        // 10. RSVPs
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('guest_name');
            $table->string('attendance_status')->default('attending'); // 'attending', 'declined', 'tentative'
            $table->integer('attendee_count')->default(1);
            $table->text('message')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });

        // 11. Guestbook Entries
        Schema::create('guestbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('guest_name');
            $table->text('message');
            $table->string('moderation_status')->default('approved')->index(); // 'approved', 'pending', 'rejected'
            $table->timestamps();
        });

        // 12. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('order_number')->unique();
            $table->string('order_type')->default('one_time'); // 'one_time', 'subscription'
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('status')->default('pending')->index(); // 'pending', 'paid', 'failed', 'expired'
            $table->json('product_snapshot_json');
            $table->timestamps();
        });

        // 13. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider')->default('sandbox'); // 'midtrans', 'xendit', 'sandbox'
            $table->string('provider_transaction_id')->nullable()->index();
            $table->string('status')->default('pending'); // 'pending', 'settlement', 'expire', 'cancel'
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        // 14. Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('provider')->default('sandbox');
            $table->string('provider_subscription_id')->nullable()->unique();
            $table->string('status')->default('active'); // 'active', 'past_due', 'canceled'
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamps();
        });

        // 15. Webhook Events (Idempotency & Replay prevention)
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_event_id')->unique();
            $table->string('event_type');
            $table->string('status')->default('processed'); // 'processed', 'failed', 'ignored'
            $table->json('sanitized_payload_json');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 16. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // 'template.created', 'order.paid', 'invitation.published', etc.
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('guestbook_entries');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('events');
        Schema::dropIfExists('invitation_media');
        
        if (Schema::hasTable('invitations')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropForeign(['published_version_id']);
            });
        }
        
        Schema::dropIfExists('invitation_versions');
        Schema::dropIfExists('invitation_designs');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plans');
    }
};
