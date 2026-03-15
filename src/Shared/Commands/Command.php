<?php

declare(strict_types=1);

namespace App\Shared\Commands;

use Symfony\Component\Uid\Uuid;

abstract readonly class Command
{
    public readonly string $commandId;
    public readonly \DateTimeImmutable $issuedAt;

    public function __construct()
    {
        $this->commandId = Uuid::v7()->toString();
        $this->issuedAt = new \DateTimeImmutable();
    }
}
