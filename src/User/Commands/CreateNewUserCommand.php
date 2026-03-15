<?php

namespace App\User\Commands;

use App\Shared\Commands\Command;
use App\User\Inputs\CreateUserInput;

final readonly class CreateNewUserCommand extends Command
{
    public function __construct(
        public CreateUserInput $input,
    ) {
        parent::__construct();
    }
}
