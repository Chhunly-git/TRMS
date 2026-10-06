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
        Schema::create('attendance_device_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('biometric_devices')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_no'); // លេខកូដមន្ត្រីដែលម៉ាស៊ីនផ្ញើមក (employeeNoString)
            $table->string('card_no')->nullable(); // លេខកាត (ប្រសិនបើមាន)
            $table->dateTime('scan_time'); // ពេលវេលាស្កេនជាក់ស្តែង
            $table->string('verify_mode')->nullable(); // វិធីស្កេន៖ face, fingerprint, card, password
            $table->string('event_type')->nullable(); // checkIn, checkOut, access
            $table->string('ip_address')->nullable(); // IP របស់ម៉ាស៊ីនផ្ញើមក
            $table->string('status')->default('PROCESSED'); // PROCESSED, UNMATCHED, IGNORED
            $table->text('note')->nullable(); // កំណត់ចំណាំ ឬសារ error
            $table->json('raw_payload')->nullable(); // Payload ដើមពីម៉ាស៊ីន
            $table->timestamps();

            $table->index(['employee_no', 'scan_time']);
            $table->index(['user_id', 'scan_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_device_logs');
    }
};
