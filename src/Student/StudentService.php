<?php

namespace App\Student;

use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Student\Dto\StudentDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class StudentService
{

    public function __construct(
        private StudentRepository $studentRepository,
        #[Target('monolog.logger.students')]
        private LoggerInterface $logger
    ) {
    }

    public function update(StudentDto $dto): void
    {
        $this->studentRepository->update($dto);
        $this->logger->info('Данные студента обновлены', ['fields' => $dto]);
    }

    public function create(StudentDto $dto): void
    {
        $this->studentRepository->create($dto);
    }

    /**
     * @return array<Student>
     */
    public function findEditable(): array
    {
        return $this->studentRepository->findAll();
    }

    public function exists(int $id): bool
    {
        return $this->studentRepository->find($id) !== null;
    }
}
