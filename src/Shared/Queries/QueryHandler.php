<?php

namespace App\Shared\Queries;

interface QueryHandler
{
    public function __invoke(Query $query): mixed;
}
