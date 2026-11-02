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
        if (Schema::hasTable('document_workflow_configs')) {
            Schema::table('document_workflow_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('document_workflow_configs', 'view_working_group_ids')) {
                    $table->json('view_working_group_ids')->nullable()->after('view_user_ids');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('document_workflow_configs')) {
            Schema::table('document_workflow_configs', function (Blueprint $table) {
                if (Schema::hasColumn('document_workflow_configs', 'view_working_group_ids')) {
                    $table->dropColumn('view_working_group_ids');
                }
            });
        }
    }
};
