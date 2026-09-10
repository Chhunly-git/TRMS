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
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ឈ្មោះបន្ទប់ប្រជុំ
            $table->string('location')->nullable(); // ទីតាំងបន្ទប់ (ឧ. អគារ A ជាន់ទី២)
            $table->integer('capacity')->default(20); // ចំនួនផ្ទុកអតិបរមា
            $table->text('facilities')->nullable(); // បរិក្ខារបន្ទប់ (JSON / Text)
            $table->string('color', 20)->default('#3b82f6'); // ពណ៌សម្គាល់សម្រាប់ Calendar / Timetable
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete(); // អ្នកទទួលខុសត្រូវបន្ទប់
            $table->string('status', 50)->default('ACTIVE'); // ACTIVE, MAINTENANCE, INACTIVE
            $table->text('description')->nullable(); // ការពិពណ៌នា
            $table->string('image', 500)->nullable(); // រូបភាពបន្ទប់
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};
