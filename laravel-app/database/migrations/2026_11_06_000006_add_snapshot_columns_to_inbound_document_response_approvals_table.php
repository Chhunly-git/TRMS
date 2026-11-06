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
        Schema::table('inbound_document_response_approvals', function (Blueprint $table) {
            $table->string('signature_snapshot_path', 255)->nullable()->after('comment');
            $table->char('signature_snapshot_hash', 64)->nullable()->after('signature_snapshot_path');
            $table->char('content_hash', 64)->nullable()->after('signature_snapshot_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_document_response_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'signature_snapshot_path',
                'signature_snapshot_hash',
                'content_hash',
            ]);
        });
    }
};
