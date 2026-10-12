<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id')->unique(); // DEP-20240101-000001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('deposit_amount', 12, 2);
            $table->decimal('deduction_percent', 5, 2)->default(20.00);
            $table->decimal('deduction_amount', 12, 2);
            $table->decimal('net_amount', 12, 2); // after deduction
            $table->decimal('withdrawal_amount', 12, 2); // net_amount * 2
            $table->string('payment_proof')->nullable(); // uploaded screenshot
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
