<?php

declare(strict_types=1);

namespace App\User\Queries\GetUserByID;

use App\Shared\Queries\Query;
use App\Shared\Queries\QueryHandler;
use App\User\Outputs\UserOutput;
use App\User\Repositories\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: GetUserByIDQuery::class)]
final readonly class GetUserByIDQueryHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /** @param GetUserByIDQuery $query */
    public function __invoke(Query $query): ?UserOutput
    {
        return ($user = $this->repository->find($query->id))
                ? UserOutput::fromUser($user)
                : null;
    }
}
