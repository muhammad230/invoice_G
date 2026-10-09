<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    public const STATUS_DRAFT   = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID    = 'paid';

    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount',
        'total',
        'status',
        'business_name',
        'business_email',
        'business_phone',
        'business_address',
        'business_website',
        'business_tagline',
        'business_bank_name',
        'business_account_title',
        'business_account_number',
        'business_iban',
        'business_payment_method',
        'business_signature_name',
        'business_signature_title',
        'logo_data',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'tax_percent'  => 'decimal:2',
        'subtotal'     => 'integer',
        'tax_amount'   => 'integer',
        'discount'     => 'integer',
        'total'        => 'integer',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ------------------------------------------------------------------
    // Money accessors (cents → decimal)
    // ------------------------------------------------------------------

    public function getSubtotalDecimalAttribute(): float
    {
        return round($this->subtotal / 100, 2);
    }

    public function getTaxAmountDecimalAttribute(): float
    {
        return round($this->tax_amount / 100, 2);
    }

    public function getDiscountDecimalAttribute(): float
    {
        return round($this->discount / 100, 2);
    }

    public function getTotalDecimalAttribute(): float
    {
        return round($this->total / 100, 2);
    }

    // ------------------------------------------------------------------
    // Currency helpers
    // ------------------------------------------------------------------

    /**
     * Currency symbol map (mirror of the one in InvoiceController).
     */
    public static function currencies(): array
    {
        return [
            'USD' => ['symbol' => '$',  'name' => 'US Dollar'],
            'PKR' => ['symbol' => 'Rs', 'name' => 'Pakistani Rupee'],
            'EUR' => ['symbol' => '€',  'name' => 'Euro'],
            'GBP' => ['symbol' => '£',  'name' => 'British Pound'],
        ];
    }

    /**
     * Symbol for this invoice's currency (falls back to USD).
     */
    public function getCurrencySymbolAttribute(): string
    {
        $all = static::currencies();

        return $all[$this->currency]['symbol'] ?? '$';
    }

    /**
     * Format a cent amount using this invoice's currency.
     */
    public function formatCents(int $cents): string
    {
        $symbol = $this->currency_symbol;
        $value  = number_format($cents / 100, 2);

        return "{$symbol}{$value}";
    }

    /**
     * Pre-formatted strings for display.
     */
    public function getSubtotalFormattedAttribute(): string
    {
        return $this->formatCents($this->subtotal);
    }

    public function getTaxAmountFormattedAttribute(): string
    {
        return $this->formatCents($this->tax_amount);
    }

    public function getDiscountFormattedAttribute(): string
    {
        return $this->formatCents($this->discount);
    }

    public function getTotalFormattedAttribute(): string
    {
        return $this->formatCents($this->total);
    }

    // ------------------------------------------------------------------
    // Status & Overdue
    // ------------------------------------------------------------------

    /**
     * An invoice is considered overdue when it is pending and its due
     * date is before today.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->lt(Date::today());
    }

    /**
     * Display status, overriding "pending" with "overdue" when applicable.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->is_overdue) {
            return 'overdue';
        }

        return $this->status;
    }

    /**
     * Scope: pending invoices whose due date is strictly before today.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PENDING)
            ->where('due_date', '<', Date::today());
    }

    /**
     * Scope: pending and NOT overdue (strictly today or later, or no due date).
     */
    public function scopePendingNotOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PENDING)
            ->where(function (Builder $q) {
                $q->whereNull('due_date')
                  ->orWhere('due_date', '>=', Date::today());
            });
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
