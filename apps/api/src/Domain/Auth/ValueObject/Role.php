<?php

declare(strict_types=1);

namespace App\Domain\Auth\ValueObject;

final class Role
{
    public const ADMIN = 'admin';
    public const EDITOR = 'editor';

    public function __construct(
        private readonly string $value,
    ) {
        if (!in_array($value, [self::ADMIN, self::EDITOR], true)) {
            throw new \InvalidArgumentException("Invalid role: {$value}");
        }
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function editor(): self
    {
        return new self(self::EDITOR);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
