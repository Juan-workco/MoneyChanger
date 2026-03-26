<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CashFlow extends Model
{
    protected $fillable = [
        'cash_flow_code',
        'type',
        'customer_id',
        'related_customer_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'currency_id',
        'transaction_date',
        'is_backdated',
        'notes',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected $dates = [
        'transaction_date',
        'verified_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_backdated' => 'boolean',
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->cash_flow_code)) {
                $model->cash_flow_code = self::generateCode($model->type);
            }
        });
    }

    /**
     * Generate a unique cash flow code.
     * Format: YYMMDD-[Username]-[Type]XXX
     */
    public static function generateCode($type)
    {
        $date = date('ymd');
        $user = Auth::user();
        $username = $user ? $user->username : 'SYS';
        $typeUpper = strtoupper($type); // AP, AR, CTC

        // Prefix example: 250209-admin-AP
        $prefix = "{$date}-{$username}-{$typeUpper}";

        // Find the last code with this prefix to increment
        // We need to match the pattern: prefix + number
        // Simple count for today by this user and type might be enough, but prone to race conditions.
        // Let's force a count query for today.

        $count = self::whereDate('created_at', today())
            ->where('created_by', $user ? $user->id : 0)
            ->where('type', $type)
            ->count() + 1;

        return "{$prefix}" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function relatedCustomer()
    {
        return $this->belongsTo(Customer::class, 'related_customer_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function fromAccount()
    {
        return $this->belongsTo(ReceivingAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(ReceivingAccount::class, 'to_account_id');
    }
}
