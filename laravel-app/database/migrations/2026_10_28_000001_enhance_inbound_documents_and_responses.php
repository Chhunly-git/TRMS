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
        // 1. បង្កើតតារាង inbound_document_assignments សម្រាប់ចាត់ចែងទៅមនុស្សច្រើននាក់ ការិយាល័យច្រើន និងនាយកដ្ឋានច្រើន (Multi-target Dispatch & Cascading)
        if (!Schema::hasTable('inbound_document_assignments')) {
            Schema::create('inbound_document_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inbound_document_id')->constrained('inbound_documents')->cascadeOnDelete();
                $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('parent_assignment_id')->nullable()->constrained('inbound_document_assignments')->nullOnDelete();
                
                $table->string('target_type', 50); // 'DEPARTMENT', 'OFFICE', 'USER'
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                
                $table->text('instruction')->nullable(); // ចំណារ ឬការណែនាំ
                $table->string('status', 50)->default('PENDING'); // PENDING, ACKNOWLEDGED, IN_PROGRESS, COMPLETED
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. បន្ថែមជួរឈរក្នុង inbound_document_responses សម្រាប់គាំទ្រទម្រង់ របាយការណ៍ និង កំណត់បង្ហាញ (Word-like documents)
        Schema::table('inbound_document_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('inbound_document_responses', 'response_type')) {
                $table->string('response_type', 50)->default('REPORT')->after('inbound_document_id'); // 'REPORT', 'SUBMISSION_NOTE', 'LETTER'
            }
            if (!Schema::hasColumn('inbound_document_responses', 'header_organization')) {
                $table->string('header_organization', 255)->nullable()->after('title');
            }
            if (!Schema::hasColumn('inbound_document_responses', 'reference_note')) {
                $table->text('reference_note')->nullable()->after('header_organization'); // យោង...
            }
            if (!Schema::hasColumn('inbound_document_responses', 'sign_title')) {
                $table->string('sign_title', 150)->nullable()->after('content');
            }
            if (!Schema::hasColumn('inbound_document_responses', 'sign_name')) {
                $table->string('sign_name', 150)->nullable()->after('sign_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_document_assignments');

        Schema::table('inbound_document_responses', function (Blueprint $table) {
            $cols = ['response_type', 'header_organization', 'reference_note', 'sign_title', 'sign_name'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('inbound_document_responses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
