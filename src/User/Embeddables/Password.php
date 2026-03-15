<?php

declare(strict_types=1);

namespace App\User\Embeddables;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Password
{
    #[ORM\Column(length: 72)]
    private string $password;

    public function setValue(string $password): static
    {
        $this->password = password_hash(trim($password), PASSWORD_BCRYPT);

        return $this;
    }

    public function getValue(): string
    {
        return $this->password;
    }
}
