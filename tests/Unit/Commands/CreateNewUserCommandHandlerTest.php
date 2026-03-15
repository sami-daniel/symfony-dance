<?php

namespace App\Tests\Unit\Commands;

use App\User\Commands\CreateNewUserCommand;
use App\User\Commands\CreateNewUserCommandHandler;
use App\User\Entity\User;
use App\User\Exceptions\UserAlreadyExistsException;
use App\User\Inputs\CreateUserInput;
use App\User\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateNewUserCommandHandlerTest extends TestCase
{
    private function createSut(
        UserRepository $repository,
        EntityManagerInterface $entityManager,
    ): CreateNewUserCommandHandler {
        return new CreateNewUserCommandHandler($repository, $entityManager);
    }

    public function testItCreatesANewUser(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);

        $entityManager->method('getConnection')->willReturn($connection);

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        $repository->expects($this->once())->method('findByEmail')->with('john@example.com')->willReturn(null);
        $connection->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn (User $user) => 'john@example.com' === $user->getEmail()));
        $entityManager->expects($this->once())->method('flush');
        $connection->expects($this->once())->method('commit');

        ($this->createSut($repository, $entityManager))($command);
    }

    public function testItThrowsWhenUserAlreadyExists(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(new User());

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

        $repository->method('findByEmail')->willReturn(null);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));
        $connection->method('isTransactionActive')->willReturn(false);
        $connection->expects($this->never())->method('rollBack');

        $this->expectException(\RuntimeException::class);

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        ($this->createSut($repository, $entityManager))($command);
    }

    public function testItRollsBackTransactionOnFailure(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);

        $repository->method('findByEmail')->willReturn(null);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->expects($this->once())->method('rollBack');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $input = new CreateUserInput('John Doe', 'john@example.com', 'Secret123');
        $command = new CreateNewUserCommand($input);

        ($this->createSut($repository, $entityManager))($command);
    }
}
