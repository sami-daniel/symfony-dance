<?php

namespace App\User\Outputs;

use App\User\Entity\User;

final readonly class UserOutput
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?\DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromUser(User $src): self
    {
        return self($src->getId(), $src->getName(), $src->getEmail(), $src->createdAt);
    }
}
