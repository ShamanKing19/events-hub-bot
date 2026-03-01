<?php

namespace App\Repository;

use App\Entity\Student;
use App\Student\Dto\StudentDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    public function update(StudentDto $dto): Student
    {
        if (empty($dto->id)) {
            throw new \RuntimeException('Не указан id');
        }

        $student = $this->find($dto->id);
        if ($student === null) {
            throw new \RuntimeException("Студент с id $dto->id не найден");
        }

        if (isset($dto->name)) {
            $student->setName($dto->name);
        }

        $student->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->flush();

        return $student;
    }

    public function create(StudentDto $dto): Student
    {
        $student = new Student();
        $student->setName($dto->name);
        $em = $this->getEntityManager();
        $em->persist($student);
        $em->flush();

        return $student;
    }

    public function remove(int $id): void
    {
        if ($student = $this->find($id)) {
            $em = $this->getEntityManager();
            $em->remove($student);
            $em->flush();
        }
    }

    /**
     * @return array<Student>
     */
    public function findForChoice(): array
    {
        return $this->createQueryBuilder('student')
            ->orderBy('student.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
