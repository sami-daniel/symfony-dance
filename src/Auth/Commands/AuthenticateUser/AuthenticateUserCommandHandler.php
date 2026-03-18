<?php

declare(strict_types=1);

namespace App\Auth\Commands\AuthenticateUser;

use App\Auth\Entities\RefreshToken;
use App\Auth\Exceptions\InvalidCredentialsException;
use App\Auth\Outputs\LoginOutput;
use App\Auth\Shared\Utils;
use App\Shared\Commands\Command;
use App\Shared\Commands\CommandHandler;
use App\User\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(handles: AuthenticateUserCommand::class)]
final readonly class AuthenticateUserCommandHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $repository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @param AuthenticateUserCommand $command */
    public function __invoke(Command $command): LoginOutput
    {
        $pwd = $command->payload->password;
        $email = $command->payload->email;

        if (!(/** @var User $user */ $user = $this->repository->findByEmail($email)) || !$this->passwordHasher->isPasswordValid($user, $pwd)) {
            throw InvalidCredentialsException::create();
        }

        $jwt = $this->jwtManager->create($user);
        $rawToken = Utils::generateSecureToken();
        $refreshToken = new RefreshToken($user, $rawToken, new \DateTimeImmutable(Utils::REFRESH_TOKEN_TTL));

        $this->entityManager->wrapInTransaction(function () use ($refreshToken) {
            $this->entityManager->persist($refreshToken);
            $this->entityManager->flush();
        });

        return new LoginOutput($jwt, $rawToken);
    }
}
