<?php

namespace App\Event;

use App\Entity\Event;
use App\Student\Dto\EventDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class EventService
{
    public function __construct(
        private EventRepository $eventRepository,
        #[Target('monolog.logger.events')]
        private LoggerInterface $logger
    ) {
    }

    public function update(EventDto $dto): void
    {
        $this->eventRepository->update($dto);
        $this->logger->info('Данные мероприятия обновлены', ['fields' => $dto]);
    }

    public function create(EventDto $dto): void
    {
        $this->eventRepository->create($dto);
        $this->logger->info('Мероприятие создано', ['fields' => $dto]);
    }

    /**
     * @return array<EventDto>
     */
    public function findForChoice(): array
    {
        return array_map(
            static fn(Event $student) => self::entityToDto($student),
            $this->eventRepository->findForChoice()
        );
    }

    public function exists(int $id): bool
    {
        return $this->eventRepository->find($id) !== null;
    }

    public function find(int $id): ?EventDto
    {
        if ($student = $this->eventRepository->find($id)) {
            return self::entityToDto($student);
        }

        return null;
    }

    public function remove(int $id): void
    {
        $this->eventRepository->remove($id);
        $this->logger->info('Мероприятие удалено', ['fields' => $id]);
    }

    private static function entityToDto(Event $event): EventDto
    {
        return new EventDto(
            id: $event->getId(),
            name: $event->getName(),
            startDate: $event->getStartDate(),
            finishDate: $event->getFinishDate()
        );
    }
}
