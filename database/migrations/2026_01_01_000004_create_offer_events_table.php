<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_letter_id')->constrained('offer_letters')->onDelete('cascade');
            $table->string('event_type'); // created, published, viewed, acknowledged, signed, etc.
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_events');
    }
};
