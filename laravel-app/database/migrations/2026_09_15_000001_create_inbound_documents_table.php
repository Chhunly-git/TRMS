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
        Schema::create('inbound_documents', function (Blueprint $table) {
            $table->id();

            // 1. ដំណាក់កាលអ្នកទទួលឯកសារ (Receptionist)
            $table->string('general_inbound_number', 50)->nullable()->index(); // e.g. 001/26
            $table->unsignedInteger('general_inbound_seq')->nullable();
            $table->string('general_inbound_year', 4)->nullable();
            $table->date('received_date');
            $table->time('received_time');
            $table->string('deliverer_name', 255);
            $table->string('deliverer_phone', 50)->nullable();
            $table->string('sender_organization', 255);
            $table->string('external_reference_number', 100)->nullable();
            $table->date('external_document_date')->nullable();
            $table->text('title');
            $table->string('document_type', 50)->default('LETTER'); // PRAKAS, DECISION, LETTER, REPORT, INVITATION, OTHER
            $table->string('urgency', 50)->default('NORMAL'); // NORMAL, MEDIUM, URGENT, MOST_URGENT
            $table->string('confidentiality', 50)->default('NORMAL'); // NORMAL, CONFIDENTIAL, TOP_SECRET
            $table->text('receptionist_notes')->nullable();
            $table->string('original_file_path', 255)->nullable();
            $table->string('original_file_name', 255)->nullable();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();

            // 2. ដំណាក់កាលជំនួយការអគ្គនាយក (DG Assistant)
            $table->string('dg_inbound_category', 50)->nullable(); // COMPANY, MEF, FSA_REGULATOR, DEPT_GENERAL_AFFAIRS, DEPT_REGISTRATION, DEPT_RESEARCH, DEPT_LEGAL, PROJECT_ACSEP, OTHER
            $table->string('dg_inbound_prefix', 10)->nullable(); // AA, E, NF, A, R, T, L, AS
            $table->unsignedInteger('dg_inbound_seq')->nullable();
            $table->string('dg_inbound_year', 4)->nullable();
            $table->string('dg_inbound_number', 50)->nullable()->index(); // e.g. AA001/26, E001/26
            $table->date('dg_received_date')->nullable();
            $table->foreignId('dg_assistant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('dg_assistant_notes')->nullable();

            // 3. ដំណាក់កាលចំណារឯកឧត្តមអគ្គនាយក (DG Annotation)
            $table->text('dg_annotation')->nullable();
            $table->timestamp('dg_annotated_at')->nullable();
            $table->boolean('is_response_required')->default(false); // True = Path B (ត្រូវឆ្លើយតប), False = Path A (សម្រាប់ជ្រាប)
            $table->string('annotated_file_path', 255)->nullable();
            $table->string('annotated_file_name', 255)->nullable();

            // 4. ដំណាក់កាលបញ្ជូនបន្ត / ចែកចាយ (Dispatching to Recipient)
            $table->timestamp('dispatched_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_type', 50)->nullable(); // DEPARTMENT, OFFICE, OFFICER
            $table->foreignId('target_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('target_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('dispatch_notes')->nullable();

            // 5. ស្ថានភាពរួម (Status & Lifecycle)
            $table->string('status', 50)->default('RECEPTION_DRAFT')->index();
            // RECEPTION_DRAFT, SUBMITTED_TO_ASSISTANT, SUBMITTED_TO_DG, DG_ANNOTATED, DISPATCHED, IN_RESPONSE_PROGRESS, COMPLETED, CANCELLED

            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_documents');
    }
};
