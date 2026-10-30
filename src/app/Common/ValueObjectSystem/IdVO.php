<?php

namespace App\Common\ValueObjectSystem;

class IdVO extends BaseValueObject
{
    private function __construct(private int $id)
    {
        if ($id < 0) {
            throw new \InvalidArgumentException("ID must be non-negative.");
        }
    }

    public static function from(int $id): self
    {
        return new self($id);
    }

    public function value(): int
    {
        return $this->id;
    }
}
