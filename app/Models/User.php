<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_email_verified',
        'is_active',
        'wallet_balance',
        'profile_photo',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_email_verified' => 'boolean',
        'is_active' => 'boolean',
        'wallet_balance' => 'decimal:2',
    ];

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getTotalDepositedAttribute(): float
    {
        return $this->deposits()->where('status', 'approved')->sum('deposit_amount');
    }

    public function getTotalWithdrawnAttribute(): float
    {
        return $this->withdrawals()->where('status', 'paid')->sum('amount');
    }

    public function getAvailableWithdrawalAttribute(): float
    {
        $approved = $this->deposits()->where('status', 'approved')->sum('withdrawal_amount');
        $withdrawn = $this->withdrawals()->whereIn('status', ['approved', 'paid'])->sum('amount');
        return max(0, $approved - $withdrawn);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->customer_id)) {
                $last = static::withTrashed()->max('id') ?? 0;
                $user->customer_id = 'CUS-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
