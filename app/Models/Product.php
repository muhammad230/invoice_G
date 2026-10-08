<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    /**
     * User that owns this product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Price as a decimal string (e.g. "19.99").
     */
    public function getPriceDecimalAttribute(): string
    {
        return number_format($this->price / 100, 2, '.', '');
    }
}
