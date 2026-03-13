<?php

namespace App\User;

use App\Entity\User;
use App\User\Dto\CreateUserDto;
use App\User\Exception\UserAlreadyExistsException;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    public function canUseBot(int $chatId): bool
    {
        return $this->findByChatId($chatId) !== null;
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function create(CreateUserDto $dto): User
    {
        $user = $this->userRepository->create($dto);
        $this->entityManager->flush();

        return $user;
    }

    public function findByChatId(int $chatId): ?User
    {
        return $this->userRepository->findByChatId($chatId);
    }

    public function doesAnyUserExists(): bool
    {
        return $this->userRepository->doesAnyUserExists();
    }
}
