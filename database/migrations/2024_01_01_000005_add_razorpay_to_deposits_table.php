<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_method')->default('manual')->after('payment_proof');
            $table->string('razorpay_order_id')->nullable()->unique()->after('payment_method');
            $table->string('razorpay_payment_id')->nullable()->unique()->after('razorpay_order_id');
            $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
            ]);
        });
    }
};
