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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            
            // ទំនាក់ទំនងជាមួយម្ចាស់របាយការណ៍ និងព័ត៌មានស្ថាប័ន
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            
            // ព័ត៌មានសប្តាហ៍ និងកាលបរិច្ឆេទ
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('week_number'); // សប្តាហ៍ទី ១ ដល់ ៥
            $table->date('start_date');
            $table->date('end_date');
            $table->string('title'); // ចំណងជើងរបាយការណ៍
            
            // ខ្លឹមសាររបាយការណ៍
            $table->longText('completed_tasks'); // លទ្ធផលការងារសម្រេចបាន
            $table->longText('planned_tasks');   // ការងារដែលត្រូវធ្វើបន្តក្នុងសប្តាហ៍បន្ទាប់
            $table->text('challenges')->nullable(); // បញ្ហាប្រឈម និងសំណូមពរ / ដំណោះស្រាយ
            
            // ឯកសារភ្ជាប់
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            
            // ស្ថានភាពរបាយការណ៍
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->default('DRAFT');
            $table->timestamp('submitted_at')->nullable();
            
            // ចំណារ និងការពិនិត្យរបស់ថ្នាក់ដឹកនាំ
            $table->text('supervisor_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexing សម្រាប់ Query លឿន
            $table->index(['user_id', 'year', 'month']);
            $table->index(['department_id', 'office_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
