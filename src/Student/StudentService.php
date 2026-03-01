<?php

namespace App\Student;

use App\Repository\StudentRepository;
use App\Student\Dto\CreateStudentDto;

readonly class StudentService
{

    public function __construct(private StudentRepository $studentRepository)
    {
    }

    public function create(CreateStudentDto $dto): void
    {
        $this->studentRepository->create($dto);
    }
}
