<?php

declare(strict_types=1);

namespace App\Auth\Inputs;

use OpenApi\Attributes as OA;

#[OA\Schema]
readonly class LoginInput
{
    public function __construct(
        #[OA\Property(example: 'jhondoe@example.com')]
        public string $email,
        #[OA\Property(example: 'Secret123')]
        public string $password,
    ) {
    }
}
