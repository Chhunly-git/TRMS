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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('type', 50)->default('MEETING'); // MEETING, MISSION, WORKSHOP, TASK, APPOINTMENT, OTHER
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('meeting_link', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('SCHEDULED'); // SCHEDULED, IN_PROGRESS, COMPLETED, POSTPONED, CANCELLED
            $table->string('priority', 50)->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT
            $table->string('color', 20)->default('#10b981');
            $table->dateTime('remind_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start_time']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
