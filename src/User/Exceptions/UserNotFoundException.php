<?php

declare(strict_types=1);

namespace App\User\Exceptions;

final class UserNotFoundException extends \DomainException
{
    public static function fromEmail(string $email): self
    {
        return new self("User with {$email} email was not found.");
    }

    public static function fromID(int $id): self
    {
        return new self("User with {$id} identifier was not found.");
    }
}
