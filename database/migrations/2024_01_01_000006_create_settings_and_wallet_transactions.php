<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // credit | debit
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reason');
            $table->timestamps();
        });

        $now = now();
        DB::table('settings')->insert([
            ['key' => 'deduction_percent', 'value' => '20', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'payout_multiplier', 'value' => '2', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'min_deposit', 'value' => '100', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'max_deposit', 'value' => '500000', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_email', 'value' => 'support@cashflow.com', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_phone', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'payment_note', 'value' => 'Pay the deposit amount in the app. Wallet is credited with the payout after Razorpay confirms the payment.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('settings');
    }
};
