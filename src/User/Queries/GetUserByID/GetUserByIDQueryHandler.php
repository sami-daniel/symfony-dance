<?php

namespace App\User\Queries\GetUserByID;

use App\Shared\Queries\Query;
use App\Shared\Queries\QueryHandler;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: GetUserByIDQuery::class)]
final readonly class GetUserByIDQueryHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /** @param GetUserByIDQuery $query */
    public function __invoke(Query $query): ?User
    {
        return $this->repository->find($query->id);
    }
}
