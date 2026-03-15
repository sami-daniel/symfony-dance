<?php

declare(strict_types=1);

namespace App\Shared\Queries;

interface QueryHandler
{
    public function __invoke(Query $query): mixed;
}
