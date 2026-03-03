<?php

namespace App\Event;

use App\Entity\Event;
use App\Student\Dto\EventDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function update(EventDto $dto): Event
    {
        if (empty($dto->id)) {
            throw new \RuntimeException('Не указан id');
        }

        $event = $this->find($dto->id);
        if ($event === null) {
            throw new \RuntimeException("Мероприятие с id $dto->id не найдено");
        }

        if (isset($dto->name)) {
            $event->setName($dto->name);
        }
        if (isset($dto->startDate)) {
            $event->setStartDate($dto->startDate);
        }
        if (isset($dto->finishDate)) {
            $event->setStartDate($dto->finishDate);
        }

        $event->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->flush();

        return $event;
    }

    public function create(EventDto $dto): Event
    {
        $event = new Event();
        if (empty($dto->name)) {
            throw new \RuntimeException('Название является обязательным');
        }
        if (empty($dto->startDate)) {
            throw new \RuntimeException('Дата начала является обязательной');
        }
        if (empty($dto->finishDate)) {
            throw new \RuntimeException('Дата окончания является обязательной');
        }

        $event->setName($dto->name);
        $event->setStartDate($dto->startDate);
        $event->setFinishDate($dto->finishDate);

        $em = $this->getEntityManager();
        $em->persist($event);
        $em->flush();

        return $event;
    }

    public function remove(int $id): void
    {
        if ($event = $this->find($id)) {
            $em = $this->getEntityManager();
            $em->remove($event);
            $em->flush();
        }
    }

    /**
     * @return array<Event>
     */
    public function findForChoice(): array
    {
        return $this->createQueryBuilder('event')
            ->orderBy('event.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
