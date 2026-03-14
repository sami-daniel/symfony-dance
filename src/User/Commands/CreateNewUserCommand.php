<?php

namespace App\User\Commands;

use App\Shared\Commands\Command;
use App\User\Requests\CreateUserRequest;

final readonly class CreateNewUserCommand extends Command
{
    public function __construct(
        public CreateUserRequest $request,
    ) {
        parent::__construct();
    }
}
