<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class NotWithinSevenDays implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        $password_changed_at = Carbon::parse($user->password_changed_at);

        if (($password_changed_at && !$user->isReset === 0) && $password_changed_at->diffInDays(Carbon::now()) <= 7)
        {
            $fail('The :attribute was recently changed less than 7 days ago');
        }
    }
}
