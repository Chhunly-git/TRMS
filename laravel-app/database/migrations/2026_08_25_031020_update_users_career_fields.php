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
            // ១. ប្តូរឈ្មោះពី current_ministry ទៅជា current_position_date (ជាប្រភេទ date)
            if (Schema::hasColumn('users', 'current_ministry')) {
                $table->renameColumn('current_ministry', 'current_position_date');
            } else {
                $table->date('current_position_date')->nullable();
            }

            // ២. លុប Field current_unit ចោល
            if (Schema::hasColumn('users', 'current_unit')) {
                $table->dropColumn('current_unit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('current_position_date', 'current_ministry');
            $table->string('current_unit')->nullable();
        });
    }
};