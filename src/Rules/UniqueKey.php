<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class UniqueKey implements DataAwareRule, Rule
{
    /**
     * All the data under validation.
     */
    protected array $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        preg_match('/\.(?<index>\d+)\./', $attribute, $matches);
        $index = (int) Arr::get($matches, 'index', fn () => throw new InvalidArgumentException('Provided attribute is not member of an array.'));

        $key = preg_replace('/\.\d\./', '.*.', $attribute);
        $existing = array_filter(data_get($this->data, $key), static fn ($key) => $key !== $index, ARRAY_FILTER_USE_KEY);

        return ! in_array($value, $existing, true);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The provided :attribute must contain a unique value.';
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return 'unique_key';
    }
}
