<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class IncrementsByRanges implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        public array $ranges,
    ) {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        foreach ($this->ranges as $increment => $range) {
            if ($value >= $range[0] && $value <= $range[1]) {
                return $value % $increment === 0;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The value must be an increment of one of the following: '.implode(', ', array_map(
            static fn (array $range, $increment) => "$increment increments from $range[0] to $range[1]",
            $this->ranges,
            array_keys($this->ranges)
        ));
    }
}
