<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use Exception;

class PasswordResetTokenExpiredException extends Exception
{
    public function __construct()
    {
        parent::__construct('Password reset token has expired');
    }
}
