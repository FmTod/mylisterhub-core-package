<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class FloatNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        /**
         * Number of fractional values allowed.
         */
        public int $fractionalCount = 2
    ) {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $regExp = sprintf('/^\d+(\.\d{1,%d})?$/i', $this->fractionalCount);

        return (bool) preg_match($regExp, $value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The decimal value should not be grater than '.$this->fractionalCount;
    }
}
