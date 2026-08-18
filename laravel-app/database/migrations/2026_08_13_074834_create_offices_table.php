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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            // Foreign Key ភ្ជាប់ទៅតារាង departments (នាយកដ្ឋាន 1 មានការិយាល័យច្រើន)
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('code')->unique(); // កូដការិយាល័យ (ឧ. OFF-01)
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
        Schema::dropIfExists('offices');
    }
};