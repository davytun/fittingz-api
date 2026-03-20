<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMeasurementKeys implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Measurements must be a valid object.');

            return;
        }

        foreach (array_keys($value) as $key) {
            if (! preg_match('/^[a-z0-9_]+$/', $key)) {
                $fail("Measurement field '{$key}' contains invalid characters. Use only lowercase letters, numbers, and underscores.");

                return;
            }

            if (strlen($key) > 50) {
                $fail("Measurement field '{$key}' is too long. Maximum 50 characters.");

                return;
            }
        }
    }
}
