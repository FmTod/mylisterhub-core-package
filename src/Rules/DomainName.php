<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class DomainName implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return (bool) preg_match('/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?!\d*$)[a-z0-9-]+\.?)$/i', $value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Invalid domain name provided.';
    }
}
