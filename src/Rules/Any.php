<?php

namespace MyListerHub\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Any implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        protected array $rules
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->rules as $rule) {
            if (Validator::make(['value' => $value], ['value' => $rule])->passes()) {
                return;
            }
        }

        $fail('The :attribute must be one of the following: '.Str::list($this->rules, true, ', ', ' or ').'.');
    }
}
