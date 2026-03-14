<?php

namespace App\User\Requests;

use App\User\Embeddables\Password;
use App\User\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 72)]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Must have an uppercase letter.')]
        #[Assert\Regex(pattern: '/[0-9]/', message: 'Must have a number.')]
        #[Assert\Regex(pattern: '/^[A-Za-z0-9]+$/', message: 'Only letters and numbers are allowed.')]
        public string $password,
    ) {
    }

    public function toUser(): User
    {
        return (new User())
            ->setEmail($this->email)
            ->setPassword(
                new Password()
                    ->setValue($this->password)
            )
            ->setName($this->name);
    }
}
