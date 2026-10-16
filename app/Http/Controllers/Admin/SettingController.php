<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'deduction_percent' => Setting::get('deduction_percent', 20),
            'payout_multiplier' => Setting::get('payout_multiplier', 2),
            'min_deposit' => Setting::get('min_deposit', 10),
            'max_deposit' => Setting::get('max_deposit', 500000),
            'min_withdrawal' => Setting::get('min_withdrawal', 100),
            'support_email' => Setting::get('support_email', ''),
            'support_phone' => Setting::get('support_phone', ''),
            'payment_note' => Setting::get('payment_note', ''),
        ];

        $example = 1000;
        $deduction = ($example * (float) $settings['deduction_percent']) / 100;
        $payout = ($example - $deduction) * (float) $settings['payout_multiplier'];

        return view('admin.settings.edit', compact('settings', 'example', 'deduction', 'payout'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'deduction_percent' => 'required|numeric|min:0|max:90',
            'payout_multiplier' => 'required|numeric|min:1|max:10',
            'min_deposit' => 'required|numeric|min:1',
            'max_deposit' => 'required|numeric|gt:min_deposit',
            'min_withdrawal' => 'required|numeric|min:1',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'payment_note' => 'nullable|string|max:500',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, (string) $value);
        }

        return back()->with('success', 'Business settings saved. New deposits will use these rules.');
    }
}
