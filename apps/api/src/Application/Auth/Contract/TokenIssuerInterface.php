<?php

declare(strict_types=1);

namespace App\Application\Auth\Contract;

use App\Domain\Auth\Entity\User;

interface TokenIssuerInterface
{
    public function issue(User $user): string;
}
