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
        // 1. បង្កើតតារាង working_groups (ក្រុមការងារ)
        if (!Schema::hasTable('working_groups')) {
            Schema::create('working_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name_kh', 255);
                $table->string('name_en', 255)->nullable();
                $table->string('code', 50)->nullable();
                $table->string('reference_document', 255)->nullable(); // លិខិត/សេចក្តីសម្រេចបង្កើត
                $table->date('established_date')->nullable();
                $table->text('description')->nullable();
                $table->string('status', 50)->default('ACTIVE'); // ACTIVE, INACTIVE
                $table->integer('order_index')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. បង្កើតតារាង working_group_members (សមាជិកក្រុមការងារ)
        if (!Schema::hasTable('working_group_members')) {
            Schema::create('working_group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('working_group_id')->constrained('working_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role', 50)->default('MEMBER'); // LEADER, DEPUTY_LEADER, SECRETARY, PERMANENT_MEMBER, MEMBER
                $table->string('custom_role_kh', 150)->nullable(); // បញ្ជាក់តួនាទីជាក់ស្តែងបន្ថែម
                $table->date('joined_date')->nullable();
                $table->string('status', 50)->default('ACTIVE'); // ACTIVE, INACTIVE
                $table->integer('order_index')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['working_group_id', 'user_id']);
                $table->index(['user_id', 'role']);
            });
        }

        // 3. បន្ថែមជួរឈរ working_group_id ក្នុង inbound_document_assignments សម្រាប់គាំទ្រការចាត់ចែងទៅក្រុមការងារ
        if (Schema::hasTable('inbound_document_assignments')) {
            Schema::table('inbound_document_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('inbound_document_assignments', 'working_group_id')) {
                    $table->foreignId('working_group_id')->nullable()->after('user_id')->constrained('working_groups')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inbound_document_assignments')) {
            Schema::table('inbound_document_assignments', function (Blueprint $table) {
                if (Schema::hasColumn('inbound_document_assignments', 'working_group_id')) {
                    $table->dropForeign(['working_group_id']);
                    $table->dropColumn('working_group_id');
                }
            });
        }

        Schema::dropIfExists('working_group_members');
        Schema::dropIfExists('working_groups');
    }
};
