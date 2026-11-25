<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MotDePasseSgt implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $octets = strlen($value);
        if ($octets < 8) {
            $fail('Le mot de passe doit faire au moins 8 octets.');
        }
        if ($octets > 72) {
            $fail("Le mot de passe est trop long (72 caractères au maximum, moins s’il contient des accents).");
        }
    }
}
