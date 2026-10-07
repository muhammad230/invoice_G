<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Owner of this client record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Invoices issued to this client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Display name used in dropdowns and lists (name + email if available).
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->email) {
            return "{$this->name} ({$this->email})";
        }

        return $this->name;
    }

    /**
     * Count of invoices for this client.
     */
    public function getInvoiceCountAttribute(): int
    {
        return $this->invoices()->count();
    }
}
