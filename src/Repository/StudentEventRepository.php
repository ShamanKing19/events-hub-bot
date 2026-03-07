<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Student;
use App\Entity\StudentEvent;
use App\StudentEvent\Dto\StudentEventDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentEvent>
 */
class StudentEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentEvent::class);
    }

    public function create(StudentEventDto $dto): void
    {
        $em = $this->getEntityManager();
        $studentEvent = new StudentEvent();
        $studentEvent->setStudent($em->getReference(Student::class, $dto->studentId));
        $studentEvent->setEvent($em->getReference(Event::class, $dto->eventId));
        $studentEvent->setScore($dto->score);

        $em->persist($studentEvent);
        $em->flush();
    }

    /**
     * @return array<StudentEvent>
     */
    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('studentEvent')
            ->leftJoin('studentEvent.event', 'event')
            ->addSelect('event')
            ->where('studentEvent.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('event.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
