<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    public const METHOD_CASH          = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_JAZZCASH      = 'jazzcash';
    public const METHOD_EASYPAISA     = 'easypaisa';
    public const METHOD_CARD          = 'card';
    public const METHOD_OTHER         = 'other';

    protected $fillable = [
        'user_id',
        'invoice_id',
        'amount',
        'paid_on',
        'method',
        'reference',
    ];

    protected $casts = [
        'paid_on' => 'date',
        'amount'  => 'integer',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ------------------------------------------------------------------
    // Method helpers
    // ------------------------------------------------------------------

    /**
     * Supported payment methods: [value => human label].
     */
    public static function methods(): array
    {
        return [
            self::METHOD_CASH          => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_JAZZCASH      => 'JazzCash',
            self::METHOD_EASYPAISA     => 'Easypaisa',
            self::METHOD_CARD          => 'Card',
            self::METHOD_OTHER         => 'Other',
        ];
    }

    /**
     * Human-readable label for this payment's method.
     */
    public function getMethodLabelAttribute(): string
    {
        return self::methods()[$this->method] ?? ucfirst((string) $this->method);
    }

    /**
     * Format the amount using the currency of the parent invoice, so a
     * multi-currency list never shows the wrong symbol.
     */
    public function getAmountFormattedAttribute(): string
    {
        if ($this->invoice) {
            return $this->invoice->formatCents($this->amount);
        }

        return '$' . number_format($this->amount / 100, 2);
    }
}
