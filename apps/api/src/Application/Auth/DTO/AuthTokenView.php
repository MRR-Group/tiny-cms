<?php

declare(strict_types=1);

namespace App\Application\Auth\DTO;

final class AuthTokenView
{
    public function __construct(
        public readonly string $token,
        public readonly int $expiresIn = 3600,
    ) {}
}
