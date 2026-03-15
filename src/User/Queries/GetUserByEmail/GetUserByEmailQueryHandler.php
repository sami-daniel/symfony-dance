<?php

namespace App\User\Queries\GetUserByEmail;

use App\Shared\Queries\QueryHandler;
use App\Shared\Queries\QueryInterface;
use App\User\Entity\User;
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
    public function __invoke(QueryInterface $query): ?User
    {
        return $this->repository->findByEmail($query->email);
    }
}
