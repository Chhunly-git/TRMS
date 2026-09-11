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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('leave_type', [
                'ANNUAL',       // ប្រចាំឆ្នាំ
                'SHORT_TERM',   // រយៈពេលខ្លី
                'MATERNITY',    // សម្ភពមាតុភាព
                'SICK',         // ព្យាបាលជំងឺ
                'PERSONAL'      // មានកិច្ចការផ្ទាល់ខ្លួន
            ]);

            $table->date('start_date');
            $table->date('end_date');
            $table->date('resume_date');
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_type', ['FULL_DAY', 'MORNING', 'AFTERNOON'])->default('FULL_DAY');
            $table->decimal('duration_days', 5, 1)->default(1.0);

            $table->text('reason');
            $table->string('contact_phone')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();

            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_stage', 50)->default('OFFICE'); // OFFICE, DEPARTMENT, LEADERSHIP, COMPLETED

            $table->enum('status', [
                'DRAFT',
                'PENDING',
                'APPROVED',
                'REJECTED',
                'CANCELLED'
            ])->default('DRAFT');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
