<?php

declare(strict_types=1);

namespace App\Auth\Commands\RefreshToken;

use App\Auth\Entities\RefreshToken;
use App\Auth\Exceptions\InvalidRefreshTokenException;
use App\Auth\Outputs\LoginOutput;
use App\Auth\Repositories\RefreshTokenRepository;
use App\Auth\Shared\Utils;
use App\Shared\Commands\Command;
use App\Shared\Commands\CommandHandler;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: RefreshTokenCommand::class)]
final readonly class RefreshTokenCommandHandler implements CommandHandler
{
    public function __construct(
        private RefreshTokenRepository $repository,
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $jwtTokenManager,
    ) {
    }

    /** @param RefreshTokenCommand $command */
    public function __invoke(Command $command): LoginOutput
    {
        $tokenString = $command->refreshToken;
        $refreshToken = $this->repository->findByToken($tokenString);

        if (!$refreshToken || $refreshToken->isExpired()) {
            throw InvalidRefreshTokenException::create();
        }

        $user = $refreshToken->getUser();
        $jwt = $this->jwtTokenManager->create($user);

        $newTokenString = Utils::generateSecureToken();
        $newRefreshToken = new RefreshToken(
            $user,
            $newTokenString,
            $refreshToken->getExpiresAt()
        );

        $this->entityManager->wrapInTransaction(function () use ($refreshToken, $newRefreshToken) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->persist($newRefreshToken);
            $this->entityManager->flush();
        });

        return new LoginOutput($jwt, $newTokenString);
    }
}
