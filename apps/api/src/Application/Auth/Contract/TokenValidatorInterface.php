<?php

declare(strict_types=1);

namespace App\Application\Auth\Contract;

interface TokenValidatorInterface
{
    /**
     * @return array<string, mixed>|null Claims if valid, null otherwise
     */
    public function validate(string $token): ?array;
}
