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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // កូដនាយកដ្ឋាន (ឧ. DEP-01)
            $table->string('name_kh');       // ឈ្មោះជាភាសាខ្មែរ
            $table->string('name_en')->nullable(); // ឈ្មោះជាអក្សរឡាតាំង
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};