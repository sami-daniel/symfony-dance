<?php

declare(strict_types=1);

namespace App\User\Exceptions;

final class UserAlreadyExistsException extends \DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("User with email '{$email}' already exists");
    }
}
