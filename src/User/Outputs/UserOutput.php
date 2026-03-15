<?php

namespace App\User\Outputs;

use App\User\Entity\User;
use OpenApi\Attributes as OA;

#[OA\Schema]
final readonly class UserOutput
{
    public function __construct(
        #[OA\Property(description: 'User ID', example: 288)]
        public int $id,
        #[OA\Property(description: 'User name', example: 'Jhon Doe')]
        public string $name,
        #[OA\Property(description: 'User email', example: 'jhon@example.com')]
        public string $email,
        #[OA\Property(description: 'Creation date of the User', example: new \DateTimeImmutable())]
        public ?\DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromUser(User $src): self
    {
        return self($src->getId(), $src->getName(), $src->getEmail(), $src->createdAt);
    }
}
