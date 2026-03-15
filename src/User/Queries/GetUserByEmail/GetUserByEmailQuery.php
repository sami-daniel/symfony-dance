<?php

declare(strict_types=1);

namespace App\User\Queries\GetUserByEmail;

use App\Shared\Queries\Query;

final readonly class GetUserByEmailQuery extends Query
{
    public function __construct(
        public string $email,
    ) {
    }
}
