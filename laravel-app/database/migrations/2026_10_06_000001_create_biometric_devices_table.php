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
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ឈ្មោះម៉ាស៊ីន ឧ. ម៉ាស៊ីនស្កេនច្រកចូល (Main Entrance)
            $table->string('device_type')->default('HIKVISION'); // ប្រភេទម៉ាស៊ីន (HIKVISION)
            $table->string('model')->nullable(); // ម៉ូដែល ឧ. DS-K1T341, MinMoe Face Terminal
            $table->string('serial_number')->nullable(); // លេខស៊េរីម៉ាស៊ីន
            $table->string('ip_address')->nullable(); // អាសយដ្ឋាន IP ឧ. 192.168.1.200
            $table->integer('port')->default(80); // Port (ធម្មតា 80 ឬ 8000)
            $table->string('username')->default('admin'); // ឈ្មោះគណនីលើម៉ាស៊ីន
            $table->string('password')->nullable(); // ពាក្យសម្ងាត់លើម៉ាស៊ីន
            $table->string('protocol')->default('HTTP'); // HTTP ឬ HTTPS
            $table->boolean('is_active')->default(true); // ដំណើរការ ឬផ្អាក
            $table->timestamp('last_sync_at')->nullable(); // ពេលវេលា Sync ចុងក្រោយ
            $table->string('last_status')->default('UNKNOWN'); // ONLINE, OFFLINE, UNKNOWN
            $table->text('status_message')->nullable(); // សារបញ្ជាក់ស្ថានភាព
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
