<?php

declare(strict_types=1);

namespace App\Application\Auth\Contract;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $hash): bool;
}
