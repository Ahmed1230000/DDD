<?php

namespace App\Common\ValueObjectSystem;

class PasswordVO extends BaseValueObject
{
    private function __construct(private string $password)
    {
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException("Password must be at least 6 characters.");
        }
    }

    public static function from(string $password): self
    {
        return new self($password);
    }

    public function value(): string
    {
        return $this->password;
    }
}
