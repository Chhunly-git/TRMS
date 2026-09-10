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
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('preferred_room_id')->nullable()->constrained('meeting_rooms')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('meeting_rooms')->nullOnDelete();
            $table->string('title'); // ប្រធានបទកិច្ចប្រជុំ
            $table->string('leader_name'); // អ្នកដឹកនាំ / ប្រធានដឹកនាំកិច្ចប្រជុំ
            $table->date('booking_date'); // កាលបរិច្ឆេទប្រជុំ
            $table->string('start_time', 10); // ម៉ោងចាប់ផ្តើម (ឧ. 09:00)
            $table->string('end_time', 10); // ម៉ោងបញ្ចប់ (ឧ. 11:30)
            $table->dateTime('start_datetime'); // កាលបរិច្ឆេទ & ម៉ោងចាប់ផ្តើម
            $table->dateTime('end_datetime'); // កាលបរិច្ឆេទ & ម៉ោងបញ្ចប់
            $table->integer('participants_count')->default(1); // ចំនួនអ្នកចូលរួម
            $table->text('equipment_needed')->nullable(); // សម្ភារៈ ឬបរិក្ខារត្រូវការបន្ថែម
            $table->text('description')->nullable(); // កំណត់សម្គាល់បន្ថែម ឬរបៀបវារៈ
            $table->string('status', 50)->default('PENDING'); // PENDING, APPROVED, REJECTED, CANCELLED
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // អ្នកអនុម័ត/ចាត់តាំងបន្ទប់
            $table->dateTime('approved_at')->nullable();
            $table->text('manager_note')->nullable(); // កំណត់ចំណាំរបស់អ្នកគ្រប់គ្រង
            $table->text('rejection_reason')->nullable(); // មូលហេតុបដិសេធ
            $table->timestamps();

            $table->index(['booking_date', 'status']);
            $table->index(['room_id', 'start_datetime', 'end_datetime']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
