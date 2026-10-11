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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('whatsapp');
            $table->string('email')->nullable();
            $table->string('template_preference')->default('Royal 3D Envelope');
            $table->string('groom_name');
            $table->string('groom_parents')->nullable();
            $table->string('bride_name');
            $table->string('bride_parents')->nullable();
            $table->string('akad_date')->nullable();
            $table->string('akad_venue')->nullable();
            $table->string('resepsi_date')->nullable();
            $table->string('resepsi_venue')->nullable();
            $table->text('photo_drive_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('package_selected')->default('Terima Beres VIP');
            $table->string('status')->default('pending')->index(); // 'pending', 'contacted', 'in_progress', 'completed', 'cancelled'
            $table->string('created_invitation_url')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
