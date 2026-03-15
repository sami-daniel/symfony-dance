<?php

namespace App\User\Queries\GetUserByEmail;

use App\Shared\Queries\QueryInterface;

final readonly class GetUserByEmailQuery extends QueryInterface
{
    public function __construct(
        public string $email,
    ) {
    }
}
