<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects values that contain CR/LF characters. Used for email "To" and
 * "Subject" fields to prevent SMTP header injection.
 */
class NoHeaderInjection implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/[\r\n]/', $value) === 1) {
            $fail('The :attribute must not contain line breaks.');
        }
    }
}
