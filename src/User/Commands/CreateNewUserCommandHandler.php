<?php

declare(strict_types=1);

namespace App\User\Commands;

use App\Shared\Commands\Command;
use App\Shared\Commands\CommandHandler;
use App\User\Entities\User;
use App\User\Exceptions\UserAlreadyExistsException;
use App\User\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(handles: CreateNewUserCommand::class)]
final readonly class CreateNewUserCommandHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $repository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /** @param CreateNewUserCommand $command */
    public function __invoke(Command $command): User
    {
        $repository = $this->repository;
        $email = $command->input->email;
        $name = $command->input->name;
        $pwd = $command->input->password;

        if ($repository->findByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($email);
        }

        $user = new User($name, $email, $pwd);
        $pwd = $this->passwordHasher->hashPassword($user, $pwd);
        $user->setPassword($pwd);

        // TODO: Implement logging and decide a rethrow policy
        $this->entityManager->wrapInTransaction(function () use ($user) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        });

        return $user;
    }
}
