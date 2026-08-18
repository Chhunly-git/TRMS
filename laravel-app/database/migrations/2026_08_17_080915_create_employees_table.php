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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // ១. ភ្ជាប់ទៅកាន់តារាង users (Nullable: ប្រសិនបើមិនទាន់មាន User Account)
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            
            // ២. ប្រភេទបុគ្គលិក/មន្ត្រី (៤ ប្រភេទស្នូល)
            $table->enum('employee_type', [
                'CIVIL_SERVICE', // មន្ត្រីមុខងារសាធារណៈ
                'STATUTORY',     // មន្ត្រីលក្ខន្តិកៈ
                'CONTRACT',      // បុគ្គលិក/មន្ត្រីជាប់កិច្ចសន្យា
                'OTHER'          // ផ្សេងទៀត
            ])->default('CIVIL_SERVICE')->index();

            // ៣. អត្តលេខ និងឈ្មោះ
            $table->string('person_id')->unique()->nullable();          // អត្តលេខផ្ទាល់ខ្លួន
            $table->string('official_id')->nullable();                  // អត្តលេខមន្ត្រីរាជការ
            $table->string('mef_id')->nullable();                       // អត្តលេខក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ
            $table->string('organization_code')->nullable();            // លេខកូដស្ថាប័ន/អង្គភាព
            $table->string('name_kh');                                  // ឈ្មោះជាភាសាខ្មែរ
            $table->string('name_en')->nullable();                      // ឈ្មោះជាអក្សរឡាតាំង
            $table->enum('gender', ['ប្រុស', 'ស្រី']);
            $table->string('nationality')->default('ខ្មែរ');
            $table->string('ethnicity')->default('ខ្មែរ');
            $table->date('dob')->nullable();                            // ថ្ងៃខែឆ្នាំកំណើត

            // ៤. ទីកន្លែងកំណើត និងអាសយដ្ឋានបច្ចុប្បន្ន (JSON ផ្ទុក ID និងឈ្មោះ រាជធានី/ខេត្ត ក្រុង/ស្រុក/ខណ្ឌ ឃុំ/សង្កាត់ ភូមិ)
            $table->json('pob')->nullable();                            // ទីកន្លែងកំណើត
            $table->json('current_address')->nullable();                // អាសយដ្ឋានបច្ចុប្បន្ន

            // ៥. ទំនាក់ទំនង និងឯកសារសម្គាល់ខ្លួន
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->index();
            $table->string('national_id')->nullable()->unique();        // លេខអត្តសញ្ញាណប័ណ្ណ
            $table->string('passport_no')->nullable();                  // លេខលិខិតឆ្លងដែន

            // ៦. រចនាសម្ព័ន្ធតួនាទី និងអង្គភាព
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();

            // ៧. ទិន្នន័យបន្ថែមផ្សេងៗ
            $table->json('additional_info')->nullable();
            
            // ៨. ស្ថានភាព និងប្រព័ន្ធកំណត់ត្រា
            $table->enum('status', ['ACTIVE', 'DISABLED', 'RETIRED', 'RESIGNED'])->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes(); // ការពារការបាត់បង់ទិន្នន័យពេលលុប
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};