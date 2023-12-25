<?php

namespace MyListerHub\Core\Concerns\Models;

trait ModelHelpers
{
    /**
     * Call the given Closure with the current instance then return the current instance.
     */
    public function tap(?callable $callback = null): static
    {
        return tap($this, $callback);
    }

    /**
     * Apply the callback if the value is truthy.
     *
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function when($value, $callback, $default = null): mixed
    {
        if ($value) {
            return $callback($this, $value);
        }

        if ($default) {
            return $default($this, $value);
        }

        return $this;
    }
}
