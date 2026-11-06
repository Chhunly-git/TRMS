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
        Schema::table('inbound_document_responses', function (Blueprint $table) {
            $table->unsignedTinyInteger('format_version')->default(2)->after('id');
            $table->string('recipient_type', 32)->default('INTERNAL')->after('response_type');
            $table->string('recipient_text', 500)->nullable()->after('recipient_type');
            $table->json('references')->nullable()->after('recipient_text');
            $table->text('closing_phrase_1')->nullable()->after('references');
            $table->text('closing_phrase_2')->nullable()->after('closing_phrase_1');
            $table->longText('body_html')->nullable()->after('closing_phrase_2');
            $table->string('lunar_date_kh', 255)->nullable()->after('body_html');
            $table->string('solar_date_kh', 255)->nullable()->after('lunar_date_kh');
            $table->char('content_hash', 64)->nullable()->after('solar_date_kh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_document_responses', function (Blueprint $table) {
            $table->dropColumn([
                'format_version',
                'recipient_type',
                'recipient_text',
                'references',
                'closing_phrase_1',
                'closing_phrase_2',
                'body_html',
                'lunar_date_kh',
                'solar_date_kh',
                'content_hash',
            ]);
        });
    }
};
