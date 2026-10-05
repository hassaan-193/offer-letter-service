<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_letters', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique()->index();

            // Candidate Personal Info
            $table->string('candidate_name');
            $table->string('passport_number');
            $table->string('nationality');

            // Employment Info
            $table->string('designation');
            $table->string('place_of_posting');
            $table->date('offer_date');
            $table->date('validity_date');
            $table->date('joining_date')->nullable();
            $table->string('probation_period')->default('3 months');
            $table->string('contract_duration')->default('2 years');

            // Working Info
            $table->string('working_hours')->default('9 hours per day');
            $table->string('weekly_day_off')->default('Friday');
            $table->text('overtime_info')->nullable();

            // Salary
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('basic_salary_words');
            $table->decimal('other_allowances', 10, 2)->default(0);
            $table->string('other_allowances_words');
            $table->decimal('total_salary', 10, 2)->default(0);
            $table->string('total_salary_words');
            $table->string('salary_currency')->default('AED');

            // Leave & Benefits
            $table->string('annual_leave')->default('30 days per year');
            $table->string('air_ticket_allowance')->default('Economy class, once per year');
            $table->text('medical_requirements')->nullable();

            // Terms
            $table->string('notice_period')->default('30 days');
            $table->text('additional_terms')->nullable();

            // Status & Lifecycle
            $table->enum('status', [
                'draft', 'published', 'viewed', 'pending_signature',
                'accepted', 'signed', 'expired', 'revoked'
            ])->default('draft');

            // Timestamps for lifecycle events
            $table->timestamp('published_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // Candidate metadata on signing
            $table->string('candidate_ip')->nullable();
            $table->text('candidate_user_agent')->nullable();

            // Admin who created it
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letters');
    }
};
