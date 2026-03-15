<?php

declare(strict_types=1);

namespace App\Tests\Unit\Queries;

use App\User\Entity\User;
use App\User\Inputs\CreateUserInput;
use App\User\Outputs\UserOutput;
use App\User\Queries\GetUserByID\GetUserByIDQuery;
use App\User\Queries\GetUserByID\GetUserByIDQueryHandler;
use App\User\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class GetUserByIDQueryHandlerTest extends TestCase
{
    private function createSut(UserRepository $repository): GetUserByIDQueryHandler
    {
        return new GetUserByIDQueryHandler($repository);
    }

    private function makeUser(int $id, string $name, string $email): User
    {
        $user = (new CreateUserInput($name, $email, 'Secret123'))->toUser();

        $idProperty = new \ReflectionProperty(User::class, 'id');
        $idProperty->setValue($user, $id);

        return $user;
    }

    public function testItReturnsUserOutputWhenUserIsFound(): void
    {
        $repository = $this->createMock(UserRepository::class);
        $user = $this->makeUser(42, 'John Doe', 'john@example.com');

        $repository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($user);

        $result = ($this->createSut($repository))(new GetUserByIDQuery(42));

        $this->assertInstanceOf(UserOutput::class, $result);
        $this->assertSame(42, $result->id);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
    }

    public function testItReturnsNullWhenUserIsNotFound(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects($this->once())
            ->method('find')
            ->with(99)
            ->willReturn(null);

        $result = ($this->createSut($repository))(new GetUserByIDQuery(99));

        $this->assertNull($result);
    }
}
