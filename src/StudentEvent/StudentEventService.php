<?php

namespace App\StudentEvent;

use App\Repository\StudentEventRepository;
use App\StudentEvent\Dto\StudentEventDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class StudentEventService
{
    public function __construct(
        private StudentEventRepository $studentEventRepository,
        #[Target('monolog.logger.actions')]
        private LoggerInterface $logger
    ) {
    }

    public function create(StudentEventDto $dto): void
    {
        $this->studentEventRepository->create($dto);
        $this->logger->info('Участие студента в мероприятии отмечено', ['fields' => $dto]);
    }
}
