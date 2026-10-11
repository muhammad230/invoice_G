<?php

namespace App\Models;

use Database\Factories\InvoiceEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single emailed copy of an invoice (or a reminder) stored for auditing
 * and shown in the invoice "Email history" card.
 */
class InvoiceEmail extends Model
{
    /** @use HasFactory<InvoiceEmailFactory> */
    use HasFactory;

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_REMINDER = 'reminder';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'to_email',
        'subject',
        'type',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ------------------------------------------------------------------
    // Display helpers
    // ------------------------------------------------------------------

    /**
     * Human-readable label for the email type.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_REMINDER => 'Reminder',
            default => 'Invoice',
        };
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
