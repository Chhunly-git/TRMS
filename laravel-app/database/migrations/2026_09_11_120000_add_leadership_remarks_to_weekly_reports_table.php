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
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->text('leadership_remarks')->nullable()->after('supervisor_remarks');
            $table->foreignId('leadership_reviewed_by')->nullable()->after('reviewed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('leadership_reviewed_at')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropForeign(['leadership_reviewed_by']);
            $table->dropColumn(['leadership_remarks', 'leadership_reviewed_by', 'leadership_reviewed_at']);
        });
    }
};
