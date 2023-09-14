<?php

namespace MyListerHub\Core\Dto;

use Spatie\LaravelData\Data;

class MediaData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public int $size,
        public string $url,
        public string $ext,
        public string $type,
    ) {
    }
}
