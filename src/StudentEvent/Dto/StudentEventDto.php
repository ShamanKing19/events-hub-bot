<?php

namespace App\StudentEvent\Dto;

readonly class StudentEventDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $studentId = null,
        public ?int $eventId = null,
        public ?float $score = null,
    ) {
    }
}
