<?php

namespace App\User\Queries\GetUserByID;

use App\Shared\Queries\QueryInterface;

final readonly class GetUserByIDQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
