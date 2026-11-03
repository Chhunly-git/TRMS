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
        // 1. បន្ថែមជួរឈរក្នុង inbound_documents សម្រាប់គាំទ្រ Assistant Review, Recall, និងការកែសម្រួលថ្មី
        Schema::table('inbound_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('inbound_documents', 'assistant_reviewed_at')) {
                $table->timestamp('assistant_reviewed_at')->nullable()->after('dg_assistant_notes');
            }
            if (!Schema::hasColumn('inbound_documents', 'assistant_reviewed_by')) {
                $table->foreignId('assistant_reviewed_by')->nullable()->after('assistant_reviewed_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inbound_documents', 'recalled_at')) {
                $table->timestamp('recalled_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('inbound_documents', 'recalled_by')) {
                $table->foreignId('recalled_by')->nullable()->after('recalled_at')->constrained('users')->nullOnDelete();
            }
        });

        // 2. បង្កើតតារាង outbound_documents (ឯកសារចេញ / របាយការណ៍ / កំណត់បង្ហាញ)
        if (!Schema::hasTable('outbound_documents')) {
            Schema::create('outbound_documents', function (Blueprint $table) {
                $table->id();
                $table->string('document_number', 100)->nullable()->index();
                $table->date('document_date');
                $table->time('document_time')->nullable();
                $table->text('title'); // កម្មវត្ថុ
                $table->string('document_type', 50)->default('REPORT'); // 'REPORT', 'MEMO', 'LETTER', 'REQUEST'
                $table->string('header_organization', 255)->nullable();
                $table->text('reference_note')->nullable(); // យោង...
                $table->longText('content')->nullable();
                $table->string('sign_title', 150)->nullable();
                $table->string('sign_name', 150)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->string('file_name', 255)->nullable();
                
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
                
                $table->foreignId('current_holder_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 50)->default('UNDER_REVIEW')->index(); // DRAFT, UNDER_REVIEW, RETURNED_TO_EDIT, COMPLETED, RECALLED
                
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. តារាងភ្ជាប់ឯកសារចូលជាឧបសម្ព័ន្ធ (Pivot: Outbound Document <-> Inbound Documents)
        if (!Schema::hasTable('outbound_document_inbound_links')) {
            Schema::create('outbound_document_inbound_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outbound_document_id')->constrained('outbound_documents')->cascadeOnDelete();
                $table->foreignId('inbound_document_id')->constrained('inbound_documents')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['outbound_document_id', 'inbound_document_id'], 'outbound_inbound_link_unique');
            });
        }

        // 4. តារាងចរន្ត និងប្រវត្តិនៃការឆ្លងឯកសារចេញ (Outbound Movements)
        if (!Schema::hasTable('outbound_document_movements')) {
            Schema::create('outbound_document_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('outbound_document_id')->constrained('outbound_documents')->cascadeOnDelete();
                $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 50); // 'SUBMITTED', 'FORWARDED', 'RETURNED_TO_EDIT', 'RECALLED', 'APPROVED'
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_document_movements');
        Schema::dropIfExists('outbound_document_inbound_links');
        Schema::dropIfExists('outbound_documents');

        Schema::table('inbound_documents', function (Blueprint $table) {
            $cols = ['assistant_reviewed_at', 'assistant_reviewed_by', 'recalled_at', 'recalled_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('inbound_documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
