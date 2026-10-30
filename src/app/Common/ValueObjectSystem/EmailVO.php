<?php

namespace App\Common\ValueObjectSystem;

class EmailVO extends BaseValueObject
{
    private function __construct(private string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format.");
        }
    }

    public static function from(string $email): self
    {
        return new self($email);
    }

    public function value(): string
    {
        return $this->email;
    }
}
