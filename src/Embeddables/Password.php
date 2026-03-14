<?php

namespace App\Embeddables;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Password {
    #[ORM\Column(length: 72)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 72)]
    #[Assert\Regex(pattern: '/[A-Z]/', message: 'Must have an uppercase letter.')]
    #[Assert\Regex(pattern: '/[0-9]/', message: 'Must have a number.')]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9]+$/', message: 'Only letters and numbers are allowed.')]
    private string $password;

    public function setPassword(string $password): static {
        $this->password = password_hash(trim($password), PASSWORD_BCRYPT);
        return $this;
    }

    public function getPassword(): string {
        return $this->password;
    }
}
