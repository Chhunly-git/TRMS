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
        if (!Schema::hasTable('document_number_counters')) {
            Schema::create('document_number_counters', function (Blueprint $table) {
                $table->id();
                $table->string('issuing_unit_type', 50); // 'DEPARTMENT', 'WORKING_GROUP'
                $table->unsignedBigInteger('issuing_unit_id');
                $table->unsignedSmallInteger('year');
                $table->unsignedInteger('last_number')->default(0);
                $table->timestamps();

                $table->unique(['issuing_unit_type', 'issuing_unit_id', 'year'], 'doc_counters_unit_year_unique');
                $table->index(['issuing_unit_type', 'issuing_unit_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_number_counters');
    }
};
