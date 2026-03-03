<?php

namespace App\Student;

use App\Entity\Student;
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
        $this->logger->info('Студент создан', ['fields' => $dto]);
    }

    /**
     * @return array<StudentDto>
     */
    public function findForChoice(): array
    {
        return array_map(
            static fn(Student $student) => self::entityToDto($student),
            $this->studentRepository->findForChoice()
        );
    }

    public function exists(int $id): bool
    {
        return $this->studentRepository->find($id) !== null;
    }

    public function find(int $id): ?StudentDto
    {
        if ($student = $this->studentRepository->find($id)) {
            return self::entityToDto($student);
        }

        return null;
    }

    public function remove(int $id): void
    {
        $this->studentRepository->remove($id);
        $this->logger->info('Студент удалён', ['id' => $id]);
    }

    private static function entityToDto(Student $student): StudentDto
    {
        return new StudentDto(
            id: $student->getId(),
            name: $student->getName()
        );
    }
}
