<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;

abstract class Uuid
{
    protected string $value;

    public function __construct(string $value)
    {
        if (!RamseyUuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid UUID: {$value}");
        }
        $this->value = $value;
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

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value;
    }
}
