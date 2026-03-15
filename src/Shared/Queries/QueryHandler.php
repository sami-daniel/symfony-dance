<?php

namespace App\Shared\Queries;

interface QueryHandler
{
    public function __invoke(QueryInterface $query): mixed;
}
