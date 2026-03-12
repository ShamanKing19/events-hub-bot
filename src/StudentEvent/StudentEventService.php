<?php

namespace App\StudentEvent;

use App\Entity\StudentEvent;
use App\Repository\StudentEventRepository;
use App\Student\StudentService;
use App\StudentEvent\Dto\StudentEventDto;
use App\StudentEvent\Dto\StudentScoreDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class StudentEventService
{
    public function __construct(
        private StudentEventRepository $studentEventRepository,
        #[Target('monolog.logger.actions')]
        private LoggerInterface $logger
    ) {}

    public function create(StudentEventDto $dto): void
    {
        $this->studentEventRepository->create($dto);
        $this->logger->info('Участие студента в мероприятии отмечено', ['fields' => $dto]);
    }

    /**
     * @return array<StudentEvent>
     */
    public function findByStudent(int $studentId): array
    {
        return $this->studentEventRepository->findByStudent($studentId);
    }

    /**
     * @return array<StudentEvent>
     */
    public function findByEvent(int $eventId): array
    {
        return $this->studentEventRepository->findByEvent($eventId);
    }

    /**
     * @return array<StudentScoreDto>
     */
    public function getTopStudents(int $limit = 10): array
    {
        $result = $this->studentEventRepository->getTopStudents($limit);
        $items = [];

        /** @var array<StudentEvent|float> $item */
        foreach ($result as $item) {
            $items[] = new StudentScoreDto(
                student: StudentService::entityToDto($item[0]->getStudent()),
                score: $item['total_score']
            );
        }

        return $items;
    }
}
