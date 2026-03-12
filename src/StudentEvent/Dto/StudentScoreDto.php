<?php

namespace App\StudentEvent\Dto;

use App\Student\Dto\StudentDto;

final readonly class StudentScoreDto
{
    public function __construct(
        public StudentDto $student,
        public float $score
    ) {}
}
