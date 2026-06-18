<?php

declare(strict_types=1);

namespace App\Auth\Outputs;

use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class LoginOutput
{
    public function __construct(
        #[OA\Property(example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY...')]
        public string $token,
        #[OA\Property(example: '8f4d9c3a2e5b41a6b8c5c0c7d1f...')]
        public string $refreshToken,
    ) {
    }
}
