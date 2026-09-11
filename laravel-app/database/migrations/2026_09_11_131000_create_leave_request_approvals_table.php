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
        Schema::create('leave_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // អ្នកចារ / ពិនិត្យ
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', [
                'SUBMITTED',
                'FORWARDED',
                'APPROVED',
                'REJECTED',
                'RETURNED',
                'CANCELLED'
            ]);
            $table->text('remarks')->nullable(); // ចំណារ ឬ មតិយោបល់
            $table->foreignId('forwarded_to_id')->nullable()->constrained('users')->nullOnDelete(); // អ្នកទទួលពិនិត្យបន្ត
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_approvals');
    }
};
