<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inbound_document_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_document_id')->constrained('inbound_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50); // REGISTERED, FORWARDED_TO_ASSISTANT, ASSISTANT_RECEIVED, SUBMITTED_TO_DG, DG_ANNOTATED, DISPATCHED, ACKNOWLEDGED, RESPONSE_DRAFTED, RESPONSE_FORWARDED, RESPONSE_APPROVED, RETURNED, CANCELLED
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->text('comment')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_document_movements');
    }
};
