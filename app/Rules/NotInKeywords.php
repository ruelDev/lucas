<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotInKeywords implements ValidationRule
{
    protected $keywords = [
        'password',
        '12345678',
        'bmi!@12345'
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = strtolower($value);

        foreach ($this->keywords as $keyword) {
            if (str_contains($password, strtolower($keyword))) {
                $fail('The :attribute should not contain common or insecure keywords.');
                return;
            }
        }
    }
}
