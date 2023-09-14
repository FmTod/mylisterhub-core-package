<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class HexColor implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return (bool) preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i', $value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Color code is invalid.';
    }
}
