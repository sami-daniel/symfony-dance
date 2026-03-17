<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class InvalidCredentialsException extends UnauthorizedHttpException
{
    public static function create(): self
    {
        return new self('Invalid credentials');
    }
}
