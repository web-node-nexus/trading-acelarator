<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::put('min_deposit', '10');
        Setting::put('min_withdrawal', '100');
    }

    public function down(): void
    {
        Setting::put('min_deposit', '100');
        Setting::put('min_withdrawal', '100');
    }
};
