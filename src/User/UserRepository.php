<?php

namespace App\User;

use App\Entity\User;
use App\User\Dto\CreateUserDto;
use App\User\Exception\UserAlreadyExistsException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function create(CreateUserDto $dto): User
    {
        if ($this->findByChatId($dto->chatId)) {
            throw new UserAlreadyExistsException($dto);
        }

        if ($dto->username && $this->findByUsername($dto->username)) {
            throw new UserAlreadyExistsException($dto);
        }

        $user = new User()->setChatId($dto->chatId);
        if ($dto->username) {
            $user->setUsername($dto->username);
        }

        $this->getEntityManager()->persist($user);

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findByChatId(int $chatId): ?User
    {
        return $this->findOneBy(['chatId' => $chatId]);
    }

    public function doesAnyUserExists(): bool
    {
        return (bool)$this->createQueryBuilder('user')
            ->select('1')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
