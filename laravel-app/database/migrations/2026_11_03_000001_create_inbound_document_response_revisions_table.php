<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('inbound_document_response_revisions')) {
            Schema::create('inbound_document_response_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('response_id')->constrained('inbound_document_responses')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('revision_number')->default(1);
                $table->string('title', 500)->nullable();
                $table->string('header_organization', 255)->nullable();
                $table->text('reference_note')->nullable();
                $table->longText('content')->nullable();
                $table->string('sign_title', 255)->nullable();
                $table->string('sign_name', 255)->nullable();
                $table->text('change_summary')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_document_response_revisions');
    }
};
