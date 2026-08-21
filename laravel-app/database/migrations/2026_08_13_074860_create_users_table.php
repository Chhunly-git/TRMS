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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // ព័ត៌មានគណនី និងការ Login (អាច nullable បើសិនជាមន្ត្រីអត់ទាន់មាន Account ចូលប្រព័ន្ធ)
            $table->string('name'); // Name for system auth compatibility
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Nullable ប្រសិនបើមន្ត្រីបង្កើតថ្មីអត់ទាន់បង្កើត login
            
            // ទំនាក់ទំនងស្ថាប័ន
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            
            // ព័ត៌មានអត្តលេខ និងប្រភេទមន្ត្រី
            $table->string('employee_code')->nullable(); // អត្តលេខមន្ត្រីរាជការ
            $table->string('mef_card_number')->nullable(); // លេខប័ណ្ណសម្គាល់មន្ត្រីកសហវ
            $table->enum('employee_type', ['CIVIL_SERVICE', 'STATUTORY', 'CONTRACT', 'OTHER'])->default('CIVIL_SERVICE');

            // ព័ត៌មានផ្ទាល់ខ្លួន
            $table->string('name_kh'); // គោត្តនាម និងនាម (ភាសាខ្មែរ)
            $table->string('name_en')->nullable(); // ឈ្មោះឡាតាំង
            $table->enum('gender', ['Male', 'Female']); // ភេទ
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable(); // ស្ថានភាពគ្រួសារ
            $table->date('dob')->nullable(); // ថ្ងៃខែឆ្នាំកំណើត
            $table->text('birth_place')->nullable(); // ទីកន្លែងកំណើត
            $table->text('current_address')->nullable(); // អាសយដ្ឋានបច្ចុប្បន្ន
            
            // ទំនាក់ទំនង និងរូបភាព Profile
            $table->string('phone')->nullable();
            $table->string('profile_image')->nullable(); // រូបថតផ្ទាល់ខ្លួន
            
            // ឯកសារសម្គាល់ខ្លួន
            $table->string('national_id_number')->nullable(); // លេខអត្តសញ្ញាណប័ណ្ណ
            $table->date('national_id_expired_date')->nullable(); // កាលបរិច្ឆេទផុតកំណត់
            $table->string('passport_number')->nullable(); // លិខិតឆ្លងដែន
            $table->date('passport_expired_date')->nullable(); // កាលបរិច្ឆេទផុតកំណត់
            
            // ស្ថានភាព និងសិទ្ធិក្នុងប្រព័ន្ធ (Level/Role)
            $table->string('level')->default('USER'); // ADMIN, USER, etc.
            $table->enum('status', ['ACTIVE', 'DISABLED', 'RETIRED', 'RESIGNED', 'ENABLED'])->default('ACTIVE');
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};