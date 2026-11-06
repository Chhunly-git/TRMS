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
        Schema::create('response_opinion_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('inbound_document_responses')->onDelete('cascade');
            $table->foreignId('approval_id')->unique()->constrained('inbound_document_response_approvals')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('slot', 32); // e.g. OPINION_LEFT_1, OPINION_LEFT_2, OPINION_LEFT_3, OPINION_LEFT_4...
            $table->unsignedSmallInteger('order_seq')->default(1);
            $table->json('movement_history')->nullable();
            $table->timestamps();

            $table->unique(['response_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_opinion_placements');
    }
};
