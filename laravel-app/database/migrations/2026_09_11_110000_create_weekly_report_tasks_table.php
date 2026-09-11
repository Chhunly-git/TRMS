<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weekly_report_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_report_id')->constrained('weekly_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            
            // ព័ត៌មានកិច្ចការងារ
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED'])->default('PENDING');
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT'])->default('MEDIUM');
            $table->unsignedTinyInteger('progress_percent')->default(0); // 0 ដល់ 100
            $table->text('result_notes')->nullable(); // លទ្ធផលសម្រេចបាន ឬកំណត់សម្គាល់
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['weekly_report_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_report_tasks');
    }
};
