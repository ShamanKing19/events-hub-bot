<?php

namespace App\Student\Dto;

readonly class StudentDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null
    ) {
    }
}
