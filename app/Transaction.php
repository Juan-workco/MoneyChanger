<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_code',
        'customer_id',
        'currency_from_id',
        'currency_to_id',
        'exchange_rate_id',
        'amount_from',
        'amount_to',
        'buy_rate',
        'sell_rate',
        'payment_method',
        'status',
        'transaction_date',
        'agent_commission',
        'profit_amount',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount_from' => 'decimal:2',
        'amount_to' => 'decimal:2',
        'buy_rate' => 'decimal:6',
        'sell_rate' => 'decimal:6',
        'agent_commission' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    protected $dates = [
        'transaction_date',
        'deleted_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = self::generateTransactionCode();
            }
        });
    }

    /**
     * Generate unique transaction code
     */
    public static function generateTransactionCode()
    {
        $date = date('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'TXN-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the source currency
     */
    public function currencyFrom()
    {
        return $this->belongsTo(Currency::class, 'currency_from_id');
    }

    /**
     * Get the target currency
     */
    public function currencyTo()
    {
        return $this->belongsTo(Currency::class, 'currency_to_id');
    }

    /**
     * Get the exchange rate used
     */
    public function exchangeRate()
    {
        return $this->belongsTo(ExchangeRate::class);
    }


    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for status filter
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Calculate and update profit
     */
    public function calculateProfit()
    {
        $this->profit_amount = ($this->sell_rate - $this->buy_rate) * $this->amount_from;
        return $this->profit_amount;
    }

    /**
     * Update transaction status
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->save();

        // Update customer stats if sent
        if ($newStatus === 'sent') {
            $this->customer->updateTransactionStats();
        }
    }

    /**
     * Get transaction type (Buy/Sell)
     */
    public function getTypeAttribute()
    {
        $defaultCurrency = SystemSetting::get('default_currency', 'MYR');

        // If we are giving default currency (MYR), we are Buying foreign currency? 
        // No, if Customer gives Foreign (currency_from) and gets Local (currency_to), we are BUYING Foreign.
        // If Customer gives Local (currency_from) and gets Foreign (currency_to), we are SELLING Foreign.

        if ($this->currencyTo && $this->currencyTo->code === $defaultCurrency) {
            return 'buy';
        }

        return 'sell';
    }

    /**
     * Get applicable rate
     */
    public function getRateAttribute()
    {
        // If type is buy, show buy rate? Or always show the rate used for conversion?
        // In store(), amount_to = amount_from * sell_rate. 
        // This implies sell_rate is ALWAYS used for conversion in the current logic.
        // So let's return sell_rate for now to match the calculation.
        return $this->sell_rate;
    }
}
