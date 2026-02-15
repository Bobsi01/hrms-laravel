<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('requested_by'); // user_id of requester
            $table->string('category', 50); // personal_info, employment, payroll, government_ids
            $table->string('field_name', 100);
            $table->text('current_value')->nullable();
            $table->text('requested_value');
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, completed
            $table->integer('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('requested_by');
            $table->index('status');
        });

        Schema::create('privacy_consents', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('consent_type', 50); // data_processing, data_sharing, marketing
            $table->boolean('consented')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->integer('version')->default(1); // privacy policy version
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_correction_requests');
        Schema::dropIfExists('privacy_consents');
    }
};
