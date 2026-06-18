<?php

declare(strict_types=1);

namespace App\Auth\Inputs;

use OpenApi\Attributes as OA;

#[OA\Schema]
readonly class RefreshTokenInput
{
    public function __construct(
        #[OA\Property(example: '8f4d9c3a2e5b41a6b8c5c0c7d1f...')]
        public string $refreshToken,
    ) {
    }
}
