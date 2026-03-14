<?php

namespace App\User\Commands;

use App\Shared\Commands\Command;
use App\Shared\Commands\CommandHandler;
use App\User\Exceptions\UserAlreadyExistsException;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CreateNewUserCommand::class)]
final readonly class CreateNewUserCommandHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @param CreateNewUserCommand $command */
    public function __invoke(Command $command): void
    {
        $repository = $this->repository;
        $email = $command->request->email;

        if ($repository->findByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($email);
        }

        $user = $command->request->toUser();

        $entityManager = $this->entityManager;
        $connection = $entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Throwable $e) {
            // TODO: Implement logging and decide a rethrow policy

            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }
}
