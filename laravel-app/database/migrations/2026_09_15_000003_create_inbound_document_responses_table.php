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
        Schema::create('inbound_document_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_document_id')->constrained('inbound_documents')->cascadeOnDelete();
            $table->string('response_number', 50)->nullable();
            $table->text('title');
            $table->longText('content')->nullable();
            $table->foreignId('drafted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_stage', 50)->default('OFFICE'); // OFFICE, DEPARTMENT, LEADERSHIP, DG, COMPLETED
            $table->string('status', 50)->default('DRAFT'); // DRAFT, UNDER_REVIEW, APPROVED_BY_DG, RETURNED
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_document_responses');
    }
};
