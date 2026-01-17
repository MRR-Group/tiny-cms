<?php

declare(strict_types=1);

namespace App\Domain\Auth\Service;

use App\Domain\Auth\ValueObject\Email;

interface EmailSenderInterface
{
    public function sendPasswordResetLink(Email $recipient, string $resetToken): void;
}
