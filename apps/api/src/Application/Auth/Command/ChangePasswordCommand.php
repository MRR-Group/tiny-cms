<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Auth\ValueObject\UserId;

final class ChangePasswordCommand
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $oldPassword,
        public readonly string $newPassword,
    ) {
    }
}
