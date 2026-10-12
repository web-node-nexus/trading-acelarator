<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique(); // CUS-000001
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('user'); // user | admin
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->string('profile_photo')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
