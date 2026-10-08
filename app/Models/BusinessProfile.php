<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BusinessProfile extends Model
{
    protected $fillable = [
        'business_name',
        'email',
        'phone',
        'website',
        'address',
        'tax_number',
        'logo_path',
    ];

    /**
     * User that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Absolute URL to the stored logo (on the public disk), or null.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset(Storage::disk('public')->url($this->logo_path));
    }

    /**
     * Read the stored logo file and return it as a base64 data URI
     * (safe for DomPDF embedding, no network access inside dompdf).
     */
    public function getLogoBase64Attribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($this->logo_path)) {
                return null;
            }
            $content = $disk->get($this->logo_path);
            if ($content === false || $content === '') {
                return null;
            }
            $mime = $disk->mimeType($this->logo_path) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($content);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
