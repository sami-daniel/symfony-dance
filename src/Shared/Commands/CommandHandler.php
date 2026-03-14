<?php

namespace App\Shared\Commands;

interface CommandHandler
{
    public function __invoke(Command $command): void;
}
