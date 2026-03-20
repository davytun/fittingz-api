<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ClientContactRequired implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = request()->input('email');
        $phone = request()->input('phone');

        if (empty($email) && empty($phone)) {
            $fail('At least one contact method (email or phone) is required.');
        }
    }
}