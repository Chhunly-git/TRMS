<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Switch 11 document and institutional foreign keys from ON DELETE CASCADE to ON DELETE RESTRICT.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('inbound_document_responses', function (Blueprint $table) {
            $table->dropForeign('inbound_document_responses_drafted_by_foreign');
            $table->foreign('drafted_by', 'inbound_document_responses_drafted_by_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();

            $table->dropForeign('inbound_document_responses_inbound_document_id_foreign');
            $table->foreign('inbound_document_id', 'inbound_document_responses_inbound_document_id_foreign')
                  ->references('id')->on('inbound_documents')
                  ->restrictOnDelete();
        });

        Schema::table('inbound_document_response_approvals', function (Blueprint $table) {
            $table->dropForeign('inbound_document_response_approvals_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_response_approvals_user_id_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('inbound_document_response_revisions', function (Blueprint $table) {
            $table->dropForeign('inbound_document_response_revisions_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_response_revisions_user_id_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('inbound_documents', function (Blueprint $table) {
            $table->dropForeign('inbound_documents_registered_by_foreign');
            $table->foreign('registered_by', 'inbound_documents_registered_by_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('inbound_document_movements', function (Blueprint $table) {
            $table->dropForeign('inbound_document_movements_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_movements_user_id_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('inbound_document_assignments', function (Blueprint $table) {
            $table->dropForeign('inbound_document_assignments_assigned_by_foreign');
            $table->foreign('assigned_by', 'inbound_document_assignments_assigned_by_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('outbound_document_movements', function (Blueprint $table) {
            $table->dropForeign('outbound_document_movements_from_user_id_foreign');
            $table->foreign('from_user_id', 'outbound_document_movements_from_user_id_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('outbound_documents', function (Blueprint $table) {
            $table->dropForeign('outbound_documents_created_by_foreign');
            $table->foreign('created_by', 'outbound_documents_created_by_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('working_group_members', function (Blueprint $table) {
            $table->dropForeign('working_group_members_user_id_foreign');
            $table->foreign('user_id', 'working_group_members_user_id_foreign')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropForeign('offices_department_id_foreign');
            $table->foreign('department_id', 'offices_department_id_foreign')
                  ->references('id')->on('departments')
                  ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     * Restores ON DELETE CASCADE on the 11 foreign keys.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropForeign('offices_department_id_foreign');
            $table->foreign('department_id', 'offices_department_id_foreign')
                  ->references('id')->on('departments')
                  ->cascadeOnDelete();
        });

        Schema::table('working_group_members', function (Blueprint $table) {
            $table->dropForeign('working_group_members_user_id_foreign');
            $table->foreign('user_id', 'working_group_members_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('outbound_documents', function (Blueprint $table) {
            $table->dropForeign('outbound_documents_created_by_foreign');
            $table->foreign('created_by', 'outbound_documents_created_by_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('outbound_document_movements', function (Blueprint $table) {
            $table->dropForeign('outbound_document_movements_from_user_id_foreign');
            $table->foreign('from_user_id', 'outbound_document_movements_from_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_document_assignments', function (Blueprint $table) {
            $table->dropForeign('inbound_document_assignments_assigned_by_foreign');
            $table->foreign('assigned_by', 'inbound_document_assignments_assigned_by_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_document_movements', function (Blueprint $table) {
            $table->dropForeign('inbound_document_movements_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_movements_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_documents', function (Blueprint $table) {
            $table->dropForeign('inbound_documents_registered_by_foreign');
            $table->foreign('registered_by', 'inbound_documents_registered_by_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_document_response_revisions', function (Blueprint $table) {
            $table->dropForeign('inbound_document_response_revisions_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_response_revisions_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_document_response_approvals', function (Blueprint $table) {
            $table->dropForeign('inbound_document_response_approvals_user_id_foreign');
            $table->foreign('user_id', 'inbound_document_response_approvals_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        Schema::table('inbound_document_responses', function (Blueprint $table) {
            $table->dropForeign('inbound_document_responses_inbound_document_id_foreign');
            $table->foreign('inbound_document_id', 'inbound_document_responses_inbound_document_id_foreign')
                  ->references('id')->on('inbound_documents')
                  ->cascadeOnDelete();

            $table->dropForeign('inbound_document_responses_drafted_by_foreign');
            $table->foreign('drafted_by', 'inbound_document_responses_drafted_by_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }
};
