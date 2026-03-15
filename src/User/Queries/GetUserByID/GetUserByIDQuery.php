<?php

declare(strict_types=1);

namespace App\User\Queries\GetUserByID;

use App\Shared\Queries\Query;

final readonly class GetUserByIDQuery extends Query
{
    public function __construct(
        public int $id,
    ) {
    }
}
