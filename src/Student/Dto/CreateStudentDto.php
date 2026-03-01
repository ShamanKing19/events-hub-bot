<?php

namespace App\Student\Dto;

readonly class CreateStudentDto
{
    public function __construct(public string $name)
    {
    }
}
