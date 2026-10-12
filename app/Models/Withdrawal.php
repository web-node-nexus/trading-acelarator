<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'txn_id',
        'user_id',
        'deposit_id',
        'amount',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'upi_id',
        'status',
        'admin_note',
        'otp_verified',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->txn_id)) {
                $last = static::withTrashed()->max('id') ?? 0;
                $withdrawal->txn_id = 'WIT-' . now()->format('Ymd') . '-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
