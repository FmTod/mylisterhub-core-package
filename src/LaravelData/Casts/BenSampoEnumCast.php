<?php

namespace MyListerHub\Core\LaravelData\Casts;

use BenSampo\Enum\Enum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class BenSampoEnumCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $context): Enum|Uncastable
    {
        /** @var class-string<\BenSampo\Enum\Enum>|null $type */
        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(Enum::class);

        if ($type === null) {
            return Uncastable::create();
        }

        try {
            return $type::fromValue($value);
        } catch (Throwable $e) {
            throw CannotCastEnum::create($type, $value);
        }
    }
}
