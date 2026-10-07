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
        Schema::create('inbound_sender_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name_kh', 255);
            $table->string('name_en', 255)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('category', 50)->default('GOVERNMENT'); // GOVERNMENT, REGULATOR, BANK, COMPANY, OTHER
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_sender_organizations');
    }
};
