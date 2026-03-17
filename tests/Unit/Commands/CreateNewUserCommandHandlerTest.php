<?php

declare(strict_types=1);

namespace App\Tests\Unit\Commands;

use App\User\Commands\CreateNewUserCommand;
use App\User\Commands\CreateNewUserCommandHandler;
use App\User\Entities\User;
use App\User\Exceptions\UserAlreadyExistsException;
use App\User\Inputs\CreateUserInput;
use App\User\Repositories\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateNewUserCommandHandlerTest extends TestCase
{
    private function createSut(
        UserRepository $repository,
        EntityManagerInterface $entityManager,
        ?UserPasswordHasherInterface $passwordHasher = null,
    ): CreateNewUserCommandHandler {
        $passwordHasher ??= $this->createStub(UserPasswordHasherInterface::class);

        return new CreateNewUserCommandHandler($repository, $entityManager, $passwordHasher);
    }

    public function testItCreatesANewUser(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $entityManager->method('getConnection')->willReturn($connection);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        $repository->expects($this->once())->method('findByEmail')->with('john@example.com')->willReturn(null);
        $connection->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn (User $user) => 'john@example.com' === $user->getUserIdentifier()));
        $entityManager->expects($this->once())->method('flush');
        $connection->expects($this->once())->method('commit');

        $result = ($this->createSut($repository, $entityManager, $passwordHasher))($command);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('john@example.com', $result->getUserIdentifier());
        $this->assertSame('John Doe', $result->getName());
    }

    public function testItThrowsWhenUserAlreadyExists(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(new User('John Doe', 'john@example.com', 'hashed'));

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'john@example.com' already exists");

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        ($this->createSut($repository, $entityManager))($command);
    }

    public function testItDoesNotRollBackWhenTransactionIsInactive(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $repository->method('findByEmail')->willReturn(null);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');
        $connection->method('isTransactionActive')->willReturn(false);
        $connection->expects($this->never())->method('rollBack');

        $this->expectException(\RuntimeException::class);

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        ($this->createSut($repository, $entityManager, $passwordHasher))($command);
    }

    public function testItRollsBackTransactionOnFailure(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);

        $repository->method('findByEmail')->willReturn(null);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->expects($this->once())->method('rollBack');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        ($this->createSut($repository, $entityManager, $passwordHasher))($command);
    }
}
