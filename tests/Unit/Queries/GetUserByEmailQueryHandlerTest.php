<?php

declare(strict_types=1);

namespace App\Tests\Unit\Queries;

use App\User\Entities\User;
use App\User\Inputs\CreateUserInput;
use App\User\Outputs\UserOutput;
use App\User\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\User\Queries\GetUserByEmail\GetUserByEmailQueryHandler;
use App\User\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;

class GetUserByEmailQueryHandlerTest extends TestCase
{
    private function createSut(UserRepository $repository): GetUserByEmailQueryHandler
    {
        return new GetUserByEmailQueryHandler($repository);
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
        $user = $this->makeUser(1, 'John Doe', 'john@example.com');

        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $result = ($this->createSut($repository))(new GetUserByEmailQuery('john@example.com'));

        $this->assertInstanceOf(UserOutput::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
    }

    public function testItReturnsNullWhenUserIsNotFound(): void
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->expects($this->once())
            ->method('findByEmail')
            ->with('unknown@example.com')
            ->willReturn(null);

        $result = ($this->createSut($repository))(new GetUserByEmailQuery('unknown@example.com'));

        $this->assertNull($result);
    }
}
