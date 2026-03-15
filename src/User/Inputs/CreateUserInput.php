<?php

namespace App\User\Inputs;

use App\User\Embeddables\Password;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema]
readonly class CreateUserInput
{
    public function __construct(
        #[OA\Property(description: 'Full name of the user', example: 'John Doe')]
        #[Assert\NotBlank(message: 'User name cannot be empty')]
        public string $name,

        #[OA\Property(description: 'Email address', example: 'john@example.com')]
        #[Assert\NotBlank]
        #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT, message: 'Email address is not valid')]
        public string $email,

        #[OA\Property(description: 'Password (min 8 chars, must include uppercase and number)', example: 'Secret123')]
        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 72)]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Password must have an uppercase letter.')]
        #[Assert\Regex(pattern: '/[0-9]/', message: 'Password must have a number.')]
        #[Assert\Regex(pattern: '/^[A-Za-z0-9]+$/', message: 'Password must have only letters and numbers.')]
        public string $password,
    ) {
    }

    public function toUser(): User
    {
        return (new User())
            ->setEmail($this->email)
            ->setPassword(
                (new Password())
                    ->setValue($this->password)
            )
            ->setName($this->name);
    }
}
