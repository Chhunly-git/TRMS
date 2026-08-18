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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title_kh');      // ឈ្មោះតួនាទីខ្មែរ (ឧ. ប្រធាននាយកដ្ឋាន, អនុប្រធាន, មន្ត្រី...)
            $table->string('title_en')->nullable(); // ឈ្មោះតួនាទីអង់គ្លេស
            $table->integer('level')->default(1);   // លំដាប់ថ្នាក់តួនាទី
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};