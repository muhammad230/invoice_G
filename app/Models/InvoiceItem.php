<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceItem extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceItemFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'integer',
        'total'    => 'integer',
    ];

    /**
     * Parent invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Per-unit price as a decimal (for display).
     */
    public function getPriceDecimalAttribute(): float
    {
        return round($this->price / 100, 2);
    }

    /**
     * Line total as a decimal (for display).
     */
    public function getTotalDecimalAttribute(): float
    {
        return round($this->total / 100, 2);
    }
}
