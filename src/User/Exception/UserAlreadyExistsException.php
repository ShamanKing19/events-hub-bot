<?php

namespace App\User\Exception;

use App\User\Dto\CreateUserDto;
use Exception;
use Throwable;

class UserAlreadyExistsException extends Exception
{
    public function __construct(public readonly CreateUserDto $dto, ?Throwable $previous = null)
    {
        parent::__construct('Пользователь уже существует', 0, $previous);
    }
}
