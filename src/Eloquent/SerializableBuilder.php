<?php

namespace MyListerHub\Core\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Traits\ForwardsCalls;
use JetBrains\PhpStorm\Pure;
use Laravie\SerializesQuery\Eloquent;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SerializableBuilder
{
    use ForwardsCalls;

    protected Builder $query;

    /**
     * Create a new instance of the SerializableBuilder class.
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Handle dynamic method calls into the builder.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardDecoratedCallTo($this->query, $method, $parameters);
    }

    /**
     * Make a new instance of the class.
     */
    #[Pure]
    public static function make(Builder $query): static
    {
        return new static($query);
    }

    /**
     * Make a new instance of the class.
     */
    #[Pure]
    public static function of(Builder $query): static
    {
        return self::make($query);
    }

    /**
     * Get an array representation of the object.
     */
    public function __serialize(): array
    {
        return Eloquent::serialize($this->query);
    }

    /**
     * Hydrate the object from an array.
     */
    public function __unserialize(array $data): void
    {
        $this->query = Eloquent::unserialize($data);
    }
}
