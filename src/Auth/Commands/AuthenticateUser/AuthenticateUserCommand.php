<?php

declare(strict_types=1);

namespace App\Auth\Commands\AuthenticateUser;

use App\Auth\Inputs\LoginInput;
use App\Shared\Commands\Command;

final readonly class AuthenticateUserCommand extends Command
{
    public function __construct(
        public LoginInput $payload,
    ) {
    }
}
