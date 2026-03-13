<?php

namespace App\User\Dto;

final readonly class CreateUserDto
{
    public function __construct(
        public int $chatId,
        public string $username
    ) {}
}
