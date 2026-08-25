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
            // ១. ព័ត៌មានបម្រើการងាររដ្ឋដំបូង
            $table->date('first_service_date')->nullable();
            $table->date('first_appointment_date')->nullable();
            $table->string('initial_framework')->nullable();
            $table->string('initial_position')->nullable();
            $table->string('initial_ministry')->nullable();
            $table->string('initial_unit')->nullable();
            $table->string('initial_department')->nullable();
            $table->string('initial_office')->nullable();

            // ២. ស្ថានភាពមុខងារបច្ចុប្បន្ន
            $table->string('current_framework')->nullable();
            $table->date('current_appointment_date')->nullable();
            $table->string('current_ministry')->nullable();
            $table->string('current_unit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_service_date',
                'first_appointment_date',
                'initial_framework',
                'initial_position',
                'initial_ministry',
                'initial_unit',
                'initial_department',
                'initial_office',
                'current_framework',
                'current_appointment_date',
                'current_ministry',
                'current_unit',
            ]);
        });
    }
};