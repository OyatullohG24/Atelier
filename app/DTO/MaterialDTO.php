<?php

namespace App\DTO;

class MaterialDTO
{
    public function __construct(
        public string $type,
        public string $image,
        public string $color_code,
        public string $code,
        public string $name,
        public string $measurement
    ) {}
}
