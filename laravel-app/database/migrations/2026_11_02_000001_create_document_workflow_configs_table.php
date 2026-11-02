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
        if (!Schema::hasTable('document_workflow_configs')) {
            Schema::create('document_workflow_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

                // 1. View Type Group (សិទ្ធិមើលឯកសារ)
                // Scopes: 'ALL', 'DEPARTMENT', 'OFFICE', 'SUBORDINATES', 'SELF_ONLY', 'CUSTOM'
                $table->string('view_type', 50)->default('SUBORDINATES');
                $table->json('view_dept_ids')->nullable();
                $table->json('view_office_ids')->nullable();
                $table->json('view_user_ids')->nullable();

                // 2. Sending Type Group (សិទ្ធិផ្ញើ/បញ្ជូនឯកសារ)
                // Scopes: 'ALL', 'DEPARTMENT', 'OFFICE', 'SUBORDINATES', 'CUSTOM'
                $table->string('send_type', 50)->default('SUBORDINATES');
                $table->json('send_dept_ids')->nullable();
                $table->json('send_office_ids')->nullable();
                $table->json('send_user_ids')->nullable();
                $table->json('send_working_group_ids')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_workflow_configs');
    }
};
