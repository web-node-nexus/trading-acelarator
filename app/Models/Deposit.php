<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'txn_id',
        'user_id',
        'deposit_amount',
        'deduction_percent',
        'deduction_amount',
        'net_amount',
        'withdrawal_amount',
        'payment_proof',
        'payment_method',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'status',
        'admin_note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'deduction_percent' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'withdrawal_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function withdrawal()
    {
        return $this->hasOne(Withdrawal::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($deposit) {
            if (empty($deposit->txn_id)) {
                $last = static::withTrashed()->max('id') ?? 0;
                $deposit->txn_id = 'DEP-' . now()->format('Ymd') . '-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
