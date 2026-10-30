<?php

namespace App\Common\ValueObjectSystem;

abstract class BaseValueObject
{
    abstract public function value(): mixed;

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }
}
