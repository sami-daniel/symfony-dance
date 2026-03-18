<?php

declare(strict_types=1);

namespace App\Auth\Commands\RefreshToken;

use App\Shared\Commands\Command;

final readonly class RefreshTokenCommand extends Command
{
    public function __construct(
        public string $refreshToken,
    ) {
    }
}
