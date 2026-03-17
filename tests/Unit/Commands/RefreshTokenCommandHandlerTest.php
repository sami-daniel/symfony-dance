<?php

declare(strict_types=1);

namespace App\Tests\Unit\Commands;

use App\Auth\Commands\RefreshToken\RefreshTokenCommand;
use App\Auth\Commands\RefreshToken\RefreshTokenCommandHandler;
use App\Auth\Entities\RefreshToken;
use App\Auth\Exceptions\InvalidRefreshTokenException;
use App\Auth\Outputs\LoginOutput;
use App\Auth\Repositories\RefreshTokenRepository;
use App\User\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;

class RefreshTokenCommandHandlerTest extends TestCase
{
    private function createSut(
        RefreshTokenRepository $repository,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtTokenManager,
    ): RefreshTokenCommandHandler {
        return new RefreshTokenCommandHandler($repository, $entityManager, $jwtTokenManager);
    }

    private function makeUser(): User
    {
        return new User('John Doe', 'john@example.com', 'hashed');
    }

    private function makeRefreshToken(User $user, \DateTimeImmutable $expiresAt): RefreshToken
    {
        return new RefreshToken($user, 'old-token-string', $expiresAt);
    }

    public function testItRotatesTokenAndReturnsLoginOutput(): void
    {
        $repository = $this->createMock(RefreshTokenRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $jwtTokenManager = $this->createStub(JWTTokenManagerInterface::class);

        $user = $this->makeUser();
        $expiresAt = new \DateTimeImmutable('+180 days');
        $oldToken = $this->makeRefreshToken($user, $expiresAt);

        $repository->expects($this->once())
            ->method('findByToken')
            ->with('old-token-string')
            ->willReturn($oldToken);

        $jwtTokenManager->method('create')->willReturn('signed.jwt.token');
        $entityManager->method('wrapInTransaction')->willReturnCallback(fn (callable $fn) => $fn());

        $entityManager->expects($this->once())->method('remove')->with($oldToken);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(
                fn (RefreshToken $newToken) => $newToken->getExpiresAt() == $expiresAt
                    && 'old-token-string' !== $newToken->getToken()
                    && $newToken->getUser() === $user
            ));
        $entityManager->expects($this->once())->method('flush');

        $command = new RefreshTokenCommand('old-token-string');
        $result = ($this->createSut($repository, $entityManager, $jwtTokenManager))($command);

        $this->assertInstanceOf(LoginOutput::class, $result);
        $this->assertSame('signed.jwt.token', $result->token);
        $this->assertNotSame('old-token-string', $result->refreshToken);
        $this->assertSame(64, strlen($result->refreshToken));
    }

    public function testItThrowsWhenTokenNotFound(): void
    {
        $repository = $this->createStub(RefreshTokenRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $jwtTokenManager = $this->createStub(JWTTokenManagerInterface::class);

        $repository->method('findByToken')->willReturn(null);

        $this->expectException(InvalidRefreshTokenException::class);

        ($this->createSut($repository, $entityManager, $jwtTokenManager))(
            new RefreshTokenCommand('nonexistent-token')
        );
    }

    public function testItThrowsWhenTokenIsExpired(): void
    {
        $repository = $this->createStub(RefreshTokenRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $jwtTokenManager = $this->createStub(JWTTokenManagerInterface::class);

        $expiredToken = $this->makeRefreshToken($this->makeUser(), new \DateTimeImmutable('-1 day'));
        $repository->method('findByToken')->willReturn($expiredToken);

        $this->expectException(InvalidRefreshTokenException::class);

        ($this->createSut($repository, $entityManager, $jwtTokenManager))(
            new RefreshTokenCommand('expired-token')
        );
    }

    public function testItPreservesOriginalExpiresAtOnRotation(): void
    {
        $repository = $this->createStub(RefreshTokenRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $jwtTokenManager = $this->createStub(JWTTokenManagerInterface::class);

        $expiresAt = new \DateTimeImmutable('+180 days');
        $oldToken = $this->makeRefreshToken($this->makeUser(), $expiresAt);

        $repository->method('findByToken')->willReturn($oldToken);
        $jwtTokenManager->method('create')->willReturn('signed.jwt.token');
        $entityManager->method('wrapInTransaction')->willReturnCallback(fn (callable $fn) => $fn());

        $persisted = null;
        $entityManager->method('persist')->willReturnCallback(function (RefreshToken $t) use (&$persisted) {
            $persisted = $t;
        });

        ($this->createSut($repository, $entityManager, $jwtTokenManager))(
            new RefreshTokenCommand('old-token-string')
        );

        $this->assertEquals($expiresAt, $persisted->getExpiresAt());
    }

}
