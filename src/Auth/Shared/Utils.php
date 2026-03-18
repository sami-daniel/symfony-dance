<?php

declare(strict_types=1);

namespace App\Auth\Shared;

class Utils
{
    public const REFRESH_TOKEN_TTL = '+180 days';

    public static function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
