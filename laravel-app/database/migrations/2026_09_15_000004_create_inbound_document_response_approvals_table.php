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
        Schema::create('inbound_document_response_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('inbound_document_responses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50); // SUBMIT, FORWARD, RETURN, DG_APPROVE
            $table->string('stage', 50); // OFFICE, DEPARTMENT, LEADERSHIP, DG
            $table->text('comment')->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->foreignId('forwarded_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_document_response_approvals');
    }
};
