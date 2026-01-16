<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;

abstract class Uuid implements \Stringable
{
    final public function __construct(
        protected string $value,
    ) {
        if (!RamseyUuid::isValid($this->value)) {
            throw new \InvalidArgumentException("Invalid UUID: {$this->value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function generate(): static
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
