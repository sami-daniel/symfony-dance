<?php

declare(strict_types=1);

namespace App\Tests\Unit\Commands;

use App\Auth\Commands\AuthenticateUser\AuthenticateUserCommand;
use App\Auth\Commands\AuthenticateUser\AuthenticateUserCommandHandler;
use App\Auth\Exceptions\InvalidCredentialsException;
use App\Auth\Inputs\LoginInput;
use App\Auth\Outputs\LoginOutput;
use App\User\Entities\User;
use App\User\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticateUserCommandHandlerTest extends TestCase
{
    private function createSut(
        UserRepository $repository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        EntityManagerInterface $entityManager,
    ): AuthenticateUserCommandHandler {
        return new AuthenticateUserCommandHandler($repository, $passwordHasher, $jwtManager, $entityManager);
    }

    private function makeCommand(string $email = 'john@example.com', string $password = 'Secret123'): AuthenticateUserCommand
    {
        return new AuthenticateUserCommand(new LoginInput($email, $password));
    }

    public function testItReturnsLoginOutputOnValidCredentials(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $user = new User('John Doe', 'john@example.com', 'hashed');

        $repository->method('findByEmail')->willReturn($user);
        $passwordHasher->method('isPasswordValid')->willReturn(true);
        $jwtManager->method('create')->willReturn('signed.jwt.token');
        $entityManager->method('wrapInTransaction')->willReturnCallback(fn (callable $fn) => $fn());

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $result = ($this->createSut($repository, $passwordHasher, $jwtManager, $entityManager))(
            $this->makeCommand()
        );

        $this->assertInstanceOf(LoginOutput::class, $result);
        $this->assertSame('signed.jwt.token', $result->token);
        $this->assertSame(64, strlen($result->refreshToken));
    }

    public function testItThrowsWhenUserNotFound(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $repository->method('findByEmail')->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        ($this->createSut($repository, $passwordHasher, $jwtManager, $entityManager))(
            $this->makeCommand('unknown@example.com')
        );
    }

    public function testItThrowsWhenPasswordIsInvalid(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $repository->method('findByEmail')->willReturn(new User('John Doe', 'john@example.com', 'hashed'));
        $passwordHasher->method('isPasswordValid')->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        ($this->createSut($repository, $passwordHasher, $jwtManager, $entityManager))(
            $this->makeCommand()
        );
    }
}
