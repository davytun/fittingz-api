<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMeasurementValue implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (!is_string($value) && !is_numeric($value)) {
            $fail('Measurement value must be text or a number.');
            return;
        }

        $stringValue = (string) $value;

        // If numeric, validate range (0.1 to 999.9)
        if (is_numeric($value)) {
            $numericValue = (float) $value;
            if ($numericValue < 0.1 || $numericValue > 999.9) {
                $fail('Numeric measurement must be between 0.1 and 999.9.');
                return;
            }
        }

        // Max length for text values
        if (strlen($stringValue) > 255) {
            $fail('Measurement value is too long. Maximum 255 characters.');
            return;
        }

        // Don't allow empty strings
        if (trim($stringValue) === '') {
            $fail('Measurement value cannot be empty.');
            return;
        }
    }
}