<?php

namespace App\Repository;

use App\Entity\Student;
use App\Student\Dto\CreateStudentDto;
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

    public function create(CreateStudentDto $dto): Student
    {
        $student = new Student();
        $student->setName($dto->name);
        $em = $this->getEntityManager();
        $em->persist($student);
        $em->flush();

        return $student;
    }
}
