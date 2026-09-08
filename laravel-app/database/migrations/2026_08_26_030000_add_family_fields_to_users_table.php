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
        Schema::table('users', function (Blueprint $table) {
            // Father Details
            $table->string('father_name')->nullable();
            $table->string('father_latin_name')->nullable();
            $table->string('father_status')->nullable();
            $table->date('father_dob')->nullable();
            $table->string('father_nationality')->nullable();
            $table->string('father_address')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_unit')->nullable();

            // Mother Details
            $table->string('mother_name')->nullable();
            $table->string('mother_latin_name')->nullable();
            $table->string('mother_status')->nullable();
            $table->date('mother_dob')->nullable();
            $table->string('mother_nationality')->nullable();
            $table->string('mother_address')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_unit')->nullable();

            // Spouse Details
            $table->string('spouse_name')->nullable();
            $table->string('spouse_latin_name')->nullable();
            $table->string('spouse_status')->nullable();
            $table->date('spouse_dob')->nullable();
            $table->string('spouse_nationality')->nullable();
            $table->string('spouse_birthplace')->nullable();
            $table->string('spouse_occupation')->nullable();
            $table->string('spouse_unit')->nullable();
            $table->string('spouse_allowance')->nullable();
            $table->string('spouse_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'father_name', 'father_latin_name', 'father_status', 'father_dob', 'father_nationality', 'father_address', 'father_occupation', 'father_unit',
                'mother_name', 'mother_latin_name', 'mother_status', 'mother_dob', 'mother_nationality', 'mother_address', 'mother_occupation', 'mother_unit',
                'spouse_name', 'spouse_latin_name', 'spouse_status', 'spouse_dob', 'spouse_nationality', 'spouse_birthplace', 'spouse_occupation', 'spouse_unit', 'spouse_allowance', 'spouse_phone'
            ]);
        });
    }
};
