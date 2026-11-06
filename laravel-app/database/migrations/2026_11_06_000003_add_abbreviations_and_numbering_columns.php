<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add abbreviation_kh to departments
        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'abbreviation_kh')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('abbreviation_kh', 50)->nullable()->after('name_en');
            });
        }

        // 2. Add abbreviation_kh to working_groups
        if (Schema::hasTable('working_groups') && !Schema::hasColumn('working_groups', 'abbreviation_kh')) {
            Schema::table('working_groups', function (Blueprint $table) {
                $table->string('abbreviation_kh', 50)->nullable()->after('name_en');
            });
        }

        // 3. Add numbering snapshot columns to inbound_document_responses
        if (Schema::hasTable('inbound_document_responses')) {
            Schema::table('inbound_document_responses', function (Blueprint $table) {
                if (!Schema::hasColumn('inbound_document_responses', 'issuing_unit_type')) {
                    $table->string('issuing_unit_type', 50)->default('DEPARTMENT')->after('office_id');
                }
                if (!Schema::hasColumn('inbound_document_responses', 'issuing_unit_id')) {
                    $table->unsignedBigInteger('issuing_unit_id')->nullable()->after('issuing_unit_type');
                }
                if (!Schema::hasColumn('inbound_document_responses', 'number_seq')) {
                    $table->unsignedInteger('number_seq')->nullable()->after('response_number');
                }
                if (!Schema::hasColumn('inbound_document_responses', 'number_year')) {
                    $table->unsignedSmallInteger('number_year')->nullable()->after('number_seq');
                }
                if (!Schema::hasColumn('inbound_document_responses', 'number_suffix')) {
                    $table->string('number_suffix', 50)->nullable()->after('number_year');
                }

                $table->index(['issuing_unit_type', 'issuing_unit_id'], 'resp_issuing_unit_idx');
                $table->unique(['issuing_unit_type', 'issuing_unit_id', 'number_year', 'number_seq'], 'resp_unit_year_seq_unique');
            });
        }

        // 4. Populate default abbreviations for existing known departments and working groups
        DB::table('departments')->where('code', 'GAD')->orWhere('id', 1)->update(['abbreviation_kh' => 'ន.ក.ទ.']);
        DB::table('departments')->where('code', 'TRC')->orWhere('id', 2)->update(['abbreviation_kh' => 'ច.ប.ច.']);
        DB::table('departments')->where('code', 'LID')->orWhere('id', 3)->update(['abbreviation_kh' => 'គ.អ.']);
        DB::table('departments')->where('code', 'TRCD')->orWhere('id', 4)->update(['abbreviation_kh' => 'ស.ប.ស.']);

        DB::table('working_groups')->where('code', 'WG-TECH')->update(['abbreviation_kh' => 'ក.ប.ច.']);
        DB::table('working_groups')->where('code', 'FinTech')->update(['abbreviation_kh' => 'ហ.ប.ច.']);

        // 5. Backfill issuing_unit_id for existing responses where department_id is set
        DB::table('inbound_document_responses')
            ->whereNotNull('department_id')
            ->whereNull('issuing_unit_id')
            ->update([
                'issuing_unit_type' => 'DEPARTMENT',
                'issuing_unit_id' => DB::raw('department_id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inbound_document_responses')) {
            Schema::table('inbound_document_responses', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropUnique('resp_unit_year_seq_unique');
                    $table->dropIndex('resp_issuing_unit_idx');
                }
                $cols = ['issuing_unit_type', 'issuing_unit_id', 'number_seq', 'number_year', 'number_suffix'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('inbound_document_responses', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('working_groups') && Schema::hasColumn('working_groups', 'abbreviation_kh')) {
            Schema::table('working_groups', function (Blueprint $table) {
                $table->dropColumn('abbreviation_kh');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'abbreviation_kh')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('abbreviation_kh');
            });
        }
    }
};
