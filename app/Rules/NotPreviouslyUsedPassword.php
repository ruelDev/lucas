<?php

namespace App\Rules;

use App\Models\PasswordArchive;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class NotPreviouslyUsedPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();

        $previousPasswords = PasswordArchive::where('user_id', $user->id)
                                ->orderBy('created_at', 'DESC')
                                ->take(5)
                                ->pluck('password');

        if (Hash::check($value, $user->password))
        {
            $fail('Your new :attribute and old :attribute should not be the same.');
        }

        foreach ($previousPasswords as $oldPasswordHash)
        {
            if (Hash::check($value, $oldPasswordHash))
            {
                $fail('The :attribute should not be the same from your last 5 used passwords.');
            }
        }
    }
}
