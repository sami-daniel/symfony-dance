<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class InvalidRefreshTokenException extends UnauthorizedHttpException
{
    public static function create(): self
    {
        return new self('Bearer', 'Invalid or expired refresh token');
    }
}
