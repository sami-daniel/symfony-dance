<?php

declare(strict_types=1);

namespace App\Shared\Commands;

interface CommandHandler
{
    public function __invoke(Command $command): mixed;
}
