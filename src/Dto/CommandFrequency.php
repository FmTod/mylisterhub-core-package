<?php

namespace MyListerHub\Core\Dto;

use Spatie\LaravelData\Data;

class CommandFrequency extends Data
{
    public function __construct(
        public string $method,
        public array $arguments = [],
    ) {
    }

    public static function fromString(string $string): self
    {
        $frequency = explode('|', $string);
        $arguments = explode(',', $frequency[1] ?? '');

        return new self($frequency[0], $arguments);
    }
}
