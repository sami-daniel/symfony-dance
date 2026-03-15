<?php

declare(strict_types=1);

namespace App\User\Queries\GetUserByEmail;

use App\Shared\Queries\Query;
use App\Shared\Queries\QueryHandler;
use App\User\Outputs\UserOutput;
use App\User\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: GetUserByEmailQuery::class)]
final readonly class GetUserByEmailQueryHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /** @param GetUserByEmailQuery $query */
    public function __invoke(Query $query): ?UserOutput
    {
        return ($user = $this->repository->findByEmail($query->email))
            ? UserOutput::fromUser($user)
            : null;
    }
}
