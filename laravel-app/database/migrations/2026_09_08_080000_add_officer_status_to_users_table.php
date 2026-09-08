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
            $table->string('officer_status')->default('ACTIVE')->after('employee_type');
            $table->date('officer_status_date')->nullable()->after('officer_status');
            $table->string('officer_status_reason', 500)->nullable()->after('officer_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['officer_status', 'officer_status_date', 'officer_status_reason']);
        });
    }
};
